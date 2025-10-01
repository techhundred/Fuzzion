"use strict";

const Fuzzion = require('../../src/js/Fuzzion.js');
const echo = console.log;

function test()
{
    const matcher = new Fuzzion();
    const tests = [
    ['jointure', 'join'],
    ['jointure', 'jiontre'],
    ['jointure', 'joitnrue'],
    ['jointure', 'turejoin']
    ];

    tests.forEach(test => {
        const string1 = test[0];
        const string2 = test[1];
        echo();
        echo('levenshtein("'+string1+'", "'+string2+'") = '+String(matcher.levenshtein(string1, string2)));
        echo('lcs("'+string1+'", "'+string2+'") = '+String(matcher.lcs(string1, string2)));
        echo('jaccard("'+string1+'", "'+string2+'") = '+String(matcher.jaccard(string1, string2)));
        echo('overlap("'+string1+'", "'+string2+'") = '+String(matcher.overlap(string1, string2)));
        echo('hamming("'+string1+'", "'+string2+'") = '+String(matcher.hamming(string1, string2)));
        echo('jaro("'+string1+'", "'+string2+'") = '+String(matcher.jaro(string1, string2)));
        echo('ngram("'+string1+'", "'+string2+'", 2) = '+String(matcher.ngram(string1, string2, 2)));
        echo('ngram("'+string1+'", "'+string2+'", 3) = '+String(matcher.ngram(string1, string2, 3)));
    });
}

test();