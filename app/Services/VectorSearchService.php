<?php

namespace App\Services;

class VectorSearchService
{
    /**
     * Tokenize text and get word frequency vector.
     */
    private function getVector(string $text): array
    {
        $text = strtolower(preg_replace('/[^a-z0-9\s]/', '', $text));
        $words = explode(' ', $text);
        $words = array_filter($words, fn($w) => strlen($w) > 2);
        
        $vector = [];
        foreach ($words as $word) {
            if (!isset($vector[$word])) {
                $vector[$word] = 0;
            }
            $vector[$word]++;
        }
        return $vector;
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
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

    /**
     * Search documents semantically.
     */
    public function search(string $query, array $documents, int $limit = 5): array
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
}
