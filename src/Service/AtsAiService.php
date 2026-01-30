<?php

namespace App\Service;

class AtsAiService
{
    /**
     * Calcule un score ATS simple basé sur les mots communs
     */
    public function score(string $cvText, string $jobText): float
    {
        $cvText  = strtolower($cvText);
        $jobText = strtolower($jobText);

        $cvWords  = array_unique(preg_split('/\W+/', $cvText));
        $jobWords = array_unique(preg_split('/\W+/', $jobText));

        if (count($jobWords) === 0) {
            return 0;
        }

        $matches = array_intersect($cvWords, $jobWords);

        $score = (count($matches) / count($jobWords)) * 100;

        return round(min($score, 100), 2);
    }
}
