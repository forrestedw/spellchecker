<?php


namespace Forrestedw\SpellChecker;


use PhpSpellcheck\Spellchecker\PHPPspell;

class SpellChecker
{
    public $language;
    public $whiteList;
    private $phpPspell;
    private $context;

    public function __construct($languages = 'en_GB')
    {
        $this->phpPspell = new PHPPspell();
        $this->language = (is_string($languages)) ? array_map('trim', explode(',', $languages)) : $languages;
        $this->context = ['from_example'];
        return $this;
    }

    public function whiteList(array $whiteList)
    {
        $this->whiteList = $whiteList;
        return $this;
    }

    /**
     * Checks the spelling in a string. Returns a correction.
     *
     * @param string $wordOrSentence
     * @return string
     * @throws \Exception
     */
    public function check(string $wordOrSentence): string
    {
        $originalWords = explode(' ', trim($wordOrSentence));

        $corrections = [];

        foreach ($originalWords as $word) {

            if ($this->isInWhiteList($word)) {
                $corrections[] = null;
                continue;
            }

            $misspellings = $this->getMisspellings($word);

            $corrections[] = ($misspellings) ? $this->correct($misspellings) : null;
        }

        return $corrections ? self::mergeInCorrections($originalWords, $corrections) : $wordOrSentence;
    }

    /**
     * Whether a word is in a given white list.
     *
     * @param string $word
     * @return bool
     */
    public function isInWhiteList(string $word): bool
    {
        if ($this->whiteList) {
            if (in_array($word, $this->whiteList)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Corrects a misspelling.
     * Returns a string if a correction is made.
     * Otherwise returns null if no suggestions.
     *
     * @param $misspellings
     * @return string|null
     */
    private function correct($misspellings)
    {
        foreach ($misspellings as $misspelling) {
            $suggestions = $misspelling->getSuggestions();

            return ($suggestions) ? $this->prioritizePreferredWords($suggestions) : null;
        }
    }

    /**
     * Get misspellings of a word.
     *
     * @param string $word
     * @return iterable
     */
    public function getMisspellings(string $word)
    {
        return $this->phpPspell->check($word, $this->language, $this->context);
    }

    /**
     * Takes $spellingSuggestions. If it contains a $preferredWord, prioritises that.
     * Otherwise, returns first $spellingSuggestion.
     *
     * @param array $spellingSuggestions
     * @return array
     */
    private function prioritizePreferredWords(array $spellingSuggestions): string
    {
        $preferredWords = ($this->whiteList) ? array_merge(self::preferredWords(), $this->whiteList) : self::preferredWords();

        $preferredCorrection = array_values(array_intersect($spellingSuggestions, $preferredWords));

        return ($preferredCorrection) ? $preferredCorrection[0] : $spellingSuggestions[0];
    }

    /**
     * Contextual common words that should be prioritised if found in correction suggestions.
     *
     * @return array
     */
    private static function preferredWords(): array
    {
        $words = ['africa', 'foundation', 'charitable'];
        $capitalized = array_map('ucfirst', $words);
        return array_merge($words, $capitalized);
    }

    /**
     * Replace misspelled words returned from with corrections returned from \PhpSpellcheck\Spellchecker\PHPPspell.
     *
     * @param array $originalWords
     * @param array $corrections
     * @return string
     * @throws \Exception
     */
    public static function mergeInCorrections(array $originalWords, array $corrections): string
    {
        $originalWordsLength = count($originalWords);
        $correctionsLength = count($corrections);

        array_values($originalWords);
        array_values($corrections);

        if ($originalWordsLength !== $correctionsLength) {
            // Something has gone wrong but we don't want to over complicate
            // how things are handled, so we just skip it by returning the
            // original spelling
            return implode(' ', $originalWords);
        }
        for ($i = 0; $i < $originalWordsLength; $i++) {
            $newSentence[] = $corrections[$i] ? $corrections[$i] : $originalWords[$i];
        }
        return implode(' ', $newSentence);
    }
}
