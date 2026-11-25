<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VectorSearchService
{
    protected $apiKey;
    protected $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
        $this->model = config('services.groq.model', 'llama-3.3-70b-versatile');
    }

    /**
     * Search documents semantically using Groq API LLM-ranking with local TF-IDF fallback.
     */
    public function search(string $query, array $documents, int $limit = 5): array
    {
        // Attempt Semantic LLM Search via Groq API
        if ($this->apiKey) {
            try {
                $llmResults = $this->searchViaLLM($query, $documents, $limit);
                if (!empty($llmResults)) {
                    return $llmResults;
                }
            } catch (\Exception $e) {
                Log::warning('Semantic Search LLM API failed. Falling back to local TF-IDF: ' . $e->getMessage());
            }
        }

        // Fallback: Local TF-IDF Cosine Similarity
        return $this->searchLocal($query, $documents, $limit);
    }

    /**
     * Call Groq API to perform semantic ranking of courses.
     */
    protected function searchViaLLM(string $query, array $documents, int $limit): array
    {
        // Keep payload small: only pass id and text for ranking
        $compactDocs = array_map(function ($doc) {
            return [
                'id' => $doc['id'],
                'text' => $doc['text']
            ];
        }, $documents);

        $systemPrompt = <<<PROMPT
Anda adalah mesin pencari semantik akademis Universitas Tangsel Raya.
Tugas Anda adalah membandingkan kata kunci pencarian (Query) dengan daftar mata kuliah, lalu menilai tingkat kemiripan makna/konsep semantik (skor 0.0 sampai 1.0) untuk setiap mata kuliah.
Kemiripan konsep dinilai dari keterkaitan materi, bukan kecocokan kata persis (misal: "membuat website" sangat mirip dengan "Pemrograman Web").

Keluaran Anda WAJIB berupa JSON object dengan key "results" berisi array objek id dan score saja, terurut dari score tertinggi ke terendah, dan hanya berisi mata kuliah dengan score > 0.1. Maksimum output adalah {$limit} item.
Jangan berikan teks penjelasan, markdown wrap, atau prolog apapun di luar JSON.

Contoh format keluaran:
{
  "results": [
    {"id": 1, "score": 0.85},
    {"id": 3, "score": 0.62}
  ]
}
PROMPT;

        $userPrompt = "Daftar Mata Kuliah:\n" . json_encode($compactDocs) . "\n\nQuery Pencarian: \"{$query}\"";

        $response = Http::timeout(10)->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt]
            ],
            'temperature' => 0.1,
            'max_tokens' => 800,
            'response_format' => ['type' => 'json_object'] // Force JSON response if supported
        ]);

        if ($response->successful()) {
            $rawText = $response->json()['choices'][0]['message']['content'] ?? '';
            
            // Clean markdown blocks if LLM still wraps it
            if (strpos($rawText, '```json') !== false || strpos($rawText, '```') !== false) {
                $rawText = str_replace(['```json', '```'], '', $rawText);
            }
            
            $ranking = json_decode(trim($rawText), true);
            
            // If json_object returned a wrapped structure (e.g. {"results": [...]} or {"courses": [...]})
            if (is_array($ranking) && !array_is_list($ranking)) {
                if (isset($ranking['results']) && is_array($ranking['results'])) {
                    $ranking = $ranking['results'];
                } else {
                    foreach ($ranking as $val) {
                        if (is_array($val) && array_is_list($val)) {
                            $ranking = $val;
                            break;
                        }
                    }
                }
            }

            if (is_array($ranking)) {
                $mappedResults = [];
                $docsById = collect($documents)->keyBy('id');
                // Re-associate scores with original document attributes (sks, semester, prodi)
                foreach ($ranking as $item) {
                    if (is_array($item) && isset($item['id'], $item['score']) && (float) $item['score'] > 0.1) {
                        $originalDoc = $docsById->get($item['id']);
                        if ($originalDoc) {
                            $originalDoc['score'] = (float) $item['score'];
                            $mappedResults[] = $originalDoc;
                        }
                    }
                }
                return array_slice($mappedResults, 0, $limit);
            }
        }

        return [];
    }

    /**
     * Local TF-IDF Fallback search.
     */
    protected function searchLocal(string $query, array $documents, int $limit): array
    {
        $results = [];
        foreach ($documents as $doc) {
            $score = $this->calculateCosineSimilarity($query, $doc['text']);
            if ($score > 0.0) {
                $doc['score'] = $score;
                $results[] = $doc;
            }
        }

        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($results, 0, $limit);
    }

    /**
     * Local Cosine Similarity logic.
     */
    private function getVector(string $text): array
    {
        $text = preg_replace('/[^a-z0-9\s]/', ' ', strtolower($text));
        $words = preg_split('/\s+/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        $words = array_filter($words, fn($w) => strlen($w) >= 2);
        
        $vector = [];
        foreach ($words as $word) {
            if (!isset($vector[$word])) {
                $vector[$word] = 0;
            }
            $vector[$word]++;
        }
        return $vector;
    }

    public function calculateCosineSimilarity(string $text1, string $text2): float
    {
        $v1 = $this->getVector($text1);
        $v2 = $this->getVector($text2);

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $uniqueWords = array_unique(array_merge(array_keys($v1), array_keys($v2)));

        foreach ($uniqueWords as $word) {
            $w1 = $v1[$word] ?? 0;
            $w2 = $v2[$word] ?? 0;
            
            $dotProduct += $w1 * $w2;
            $normA += $w1 * $w1;
            $normB += $w2 * $w2;
        }

        if ($normA == 0 || $normB == 0) {
            return 0.0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
