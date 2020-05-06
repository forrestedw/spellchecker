# Spellchecker
A convenient chainable wrapper for `tigitz/php-spellchecker`

## Usage
```php
use Forrestedw\SpellChecker\SpellChecker;

//Initiate the SpellChecker
$spellChecker = (new SpellChecker);

//Whitelist words. Optional.
$spellChecker->whiteList(['Lett','theese','spellins','goo']);


//Set preferred words to select from corrctions. Optional.
$spellChecker->prefer(['These','Words','Are','Most','Important','To','Me']);

$correction = $spellChecker->check('A word or sentence');
```
