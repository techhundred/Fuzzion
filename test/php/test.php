<?php
include(dirname(__FILE__) . '/../../src/php/Fuzzion.php');

function test()
{
    $matcher = new Fuzzion();
    $tests = [
    ['jointure', 'join'],
    ['jointure', 'jiontre'],
    ['jointure', 'joitnrue'],
    ['jointure', 'turejoin'],
    ];

    foreach ($tests as $test)
    {
        $string1 = $test[0];
        $string2 = $test[1];
        echo "\n"; echo('levenshtein("'.$string1.'", "'.$string2.'") = '.strval($matcher->levenshtein($string1, $string2)));
        echo "\n"; echo('lcs("'.$string1.'", "'.$string2.'") = '.strval($matcher->lcs($string1, $string2)));
        echo "\n"; echo('jaccard("'.$string1.'", "'.$string2.'") = '.strval($matcher->jaccard($string1, $string2)));
        echo "\n"; echo('overlap("'.$string1.'", "'.$string2.'") = '.strval($matcher->overlap($string1, $string2)));
        echo "\n"; echo('hamming("'.$string1.'", "'.$string2.'") = '.strval($matcher->hamming($string1, $string2)));
        echo "\n"; echo('jaro("'.$string1.'", "'.$string2.'") = '.strval($matcher->jaro($string1, $string2)));
        echo "\n"; echo('ngram("'.$string1.'", "'.$string2.'", 2) = '.strval($matcher->ngram($string1, $string2, 2)));
        echo "\n"; echo('ngram("'.$string1.'", "'.$string2.'", 3) = '.strval($matcher->ngram($string1, $string2, 3)));
        echo "\n";
    }
}

test();