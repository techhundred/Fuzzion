/**
*  Fuzzion
*  Fuzzy / approximate string similarity metrics for PHP, JavaScript, Python
*
*  @version: 1.0.0
*  https://github.com/foo123/Fuzzion
*
**/
!function(root, name, factory) {
"use strict";
if (('object' === typeof module) && module.exports) /* CommonJS */
    (module.$deps = module.$deps||{}) && (module.exports = module.$deps[name] = factory.call(root));
else if (('function' === typeof define) && define.amd && ('function' === typeof require) && ('function' === typeof require.specified) && require.specified(name) /*&& !require.defined(name)*/) /* AMD */
    define(name, ['module'], function(module) {factory.moduleUri = module.uri; return factory.call(root);});
else if (!(name in root)) /* Browser/WebWorker/.. */
    (root[name] = factory.call(root)||1) && ('function' === typeof(define)) && define.amd && define(function() {return root[name];});
}(  /* current root */          'undefined' !== typeof self ? self : this,
    /* module name */           "Fuzzion",
    /* module factory */        function ModuleFactory__Fuzzion(undef) {
"use strict";

/**
References:

Navarro, Gonzalo (March 2001), "A guided tour to approximate string matching"
https://www.thefreelibrary.com/A+Guided+Tour+to+Approximate+String+Matching.-a075950731

Wikipedia contributors. (2024, August 12). String metric. In Wikipedia, The Free Encyclopedia. Retrieved 19:36, September 26, 2025, from https://en.wikipedia.org/w/index.php?title=String_metric&oldid=1239983587
*/
function Fuzzion()
{
}
Fuzzion.VERSION = "1.0.0";
Fuzzion.prototype = {
    constructor: Fuzzion,

    ident: function(s1, s2) {
        return s1 === s2 ? 1 : 0;
    },

    levenshtein: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Levenshtein_distance
        // counts only insertions, deletions and substitutions

        var l1 = s1.length, l2 = s2.length, a, b, c1, c2, t, i, j, d;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        if (l2 > l1)
        {
            // swap
            t = s1;
            s1 = s2;
            s2 = t;
            t = l1;
            l1 = l2;
            l2 = t;
        }
        b = new Array(l2+1);
        a = new Array(l2+1);
        for (j=0; j<=l2; ++j) a[j] = j;

        for (i=1; i<=l1; ++i)
        {
            // swap
            t = a;
            a = b;
            b = t;
            a[0] = b[0] = i - 1;
            c1 = s1.charAt(i-1);
            for (j=1; j<=l2; ++j)
            {
                c2 = s2.charAt(j-1);
                d = c1 === c2 ? 0 : 1;
                a[j] = min(
                    b[j  ] +  1,        // deletion
                    a[j-1] +  1,        // insertion
                    b[j-1] +  d         // substitution
                );
            }
        }
        return 1 - a[l2] / l1;
    },

    damerau: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Damerau%E2%80%93Levenshtein_distance
        // counts only insertions, deletions, substitutions and adjacent transpositions

        var l1 = s1.length, l2 = s2.length, a, b, c, c1, c2, t, i, j, d;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        if (l2 > l1)
        {
            // swap
            t = s1;
            s1 = s2;
            s2 = t;
            t = l1;
            l1 = l2;
            l2 = t;
        }
        c = null;
        b = new Array(l2+1);
        a = new Array(l2+1);
        for (j=0; j<=l2; ++j) a[j] = j;

        for (i=1; i<=l1; ++i)
        {
            // swap
            c = b.slice();
            t = a;
            a = b;
            b = t;
            a[0] = b[0] = i - 1;
            c1 = s1.charAt(i-1);
            for (j=1; j<=l2; ++j)
            {
                c2 = s2.charAt(j-1);
                d = c1 === c2 ? 0 : 1;
                a[j] = min(
                    b[j  ] +  1,        // deletion
                    a[j-1] +  1,        // insertion
                    b[j-1] +  d         // substitution
                );
                if ((i > 1) && (j > 1) && (s1.charAt(i-1) === s2.charAt(j-2)) && (s1.charAt(i-2) === s2.charAt(j-1)))
                {
                    a[j] = min(
                        a[j  ],
                        c[j-2] + d     // transposition
                    );
                }
            }
        }
        return 1 - a[l2] / l1;
    },

    lcs: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Longest_common_subsequence_problem
        // counts only insertions and deletions

        var l1 = s1.length, l2 = s2.length, a, b, c1, c2, t, i, j;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        if (l2 > l1)
        {
            // swap
            t = s1;
            s1 = s2;
            s2 = t;
            t = l1;
            l1 = l2;
            l2 = t;
        }
        b = new Array(l2);
        a = new Array(l2);
        for (i=0; i<l1; ++i)
        {
            // swap
            t = a;
            a = b;
            b = t;
            c1 = s1.charAt(i);
            for (j=0; j<l2; ++j)
            {
                c2 = s2.charAt(j);
                if (c1 === c2)
                {
                    a[j] = (0 === i || 0 === j ? 0 : b[j-1]) + 1;
                }
                else
                {
                    a[j] = max(0 === j ? 0 : a[j-1], 0 === i ? 0 : b[j]);
                }
            }
        }
        return 1 - (l1 + l2 - 2*a[l2-1]) / l1;
    },

    hamming: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Hamming_distance
        // counts only substitutions and either deletions or insertions

        var l1 = s1.length, l2 = s2.length, lm, lM, d, i;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        lm = min(l1, l2);
        lM = max(l1, l2);
        d = lM - lm;
        for (i=0; i<lm; ++i)
        {
            if (s1.charAt(i) !== s2.charAt(i))
            {
                ++d;
            }
        }
        return 1 - d / lM;
    },

    jaro: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Jaro%E2%80%93Winkler_distance

        var l1 = s1.length, l2 = s2.length, l, j, jw, lp,
            match_distance, s1_matches, s2_matches,
            matches, transpositions, i, k,
            start, end, lengthOfCommonPrefix;

        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        // jaro distance
        // max distance between two chars to be considered matching
        match_distance = max(l1, l2) / 2 - 1;
        s1_matches = new Array(l1);
        s2_matches = new Array(l2);

        // number of matches and transpositions
        matches        = 0;
        transpositions = 0;

        // find the matches
        for (i=0; i<l1; ++i)
        {
            // start and end take into account the match distance
            start = floor(max(0, i - match_distance));
            end   = floor(min(i + match_distance + 1, l2));

            for (k=start; k<end; ++k)
            {
                // if str2 already has a match continue
                if (s2_matches[k]) continue;

                // if str1 and str2 are not
                if (s1.charAt(i) !== s2.charAt(k)) continue;

                // otherwise assume there is a match
                s1_matches[i] = true;
                s2_matches[k] = true;
                ++matches;
                break;
            }
        }

        if (0 === matches)
        {
            // if there are no matches return 0
            j = 0;
        }
        else
        {
            // count transpositions
            k = 0;
            for (i=0; i<l1; ++i)
            {
                // if there are no matches in str1 continue
                if (!s1_matches[i]) continue;

                // while there is no match in str2 increment k
                while (!s2_matches[k]) ++k;

                // increment transpositions
                if (s1.charAt(i) !== s2.charAt(k)) ++transpositions;

                ++k;
            }

            // divide the number of transpositions by two as per the algorithm specs
            // this division is valid because the counted transpositions include both
            // instances of the transposed characters.
            transpositions /= 2.0;

            // return the Jaro distance
            j = ((matches / l1) + (matches / l2) + ((matches - transpositions) / matches)) / 3;
        }

        // jarowinkler distance
        if (j < /*thres*/0.5)
        {
            jw = j;
        }
        else
        {
            lengthOfCommonPrefix = 0;
            l = min(l1, l2);
            for (i=0; i<l; ++i)
            {
                if (s1.charAt(i) === s2.charAt(i)) ++lengthOfCommonPrefix;
                else break;
            }
            lp = min(0.1, 1.0 / max(l1, l2)) * lengthOfCommonPrefix;
            jw = lp + j * (1 - lp);
        }
        return jw;
    },

    jaccard: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Jaccard_index

        var l1 = s1.length, l2 = s2.length, lookup, intersection, union, i, c;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        lookup = {};
        intersection = 0;
        union = 0;
        for (i=0; i<l1; ++i)
        {
            c = s1.charAt(i);
            if (isset(lookup, c) && (1 === lookup[c])) continue;
            lookup[c] = 1;
            ++union;
        }
        for (i=0; i<l2; ++i)
        {
            c = s2.charAt(i);
            if (isset(lookup, c) && (1 === lookup[c]))
            {
                ++intersection;
            }
            else
            {
                ++union;
            }
        }
        return intersection / union;
    },

    overlap: function(s1, s2) {
        // https://en.wikipedia.org/wiki/Overlap_coefficient

        var l1 = s1.length, l2 = s2.length, lookup, intersection, i, c;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        lookup = {};
        intersection = 0;
        for (i=0; i<l1; ++i)
        {
            c = s1.charAt(i);
            if (isset(lookup, c) && (1 === lookup[c])) continue;
            lookup[c] = 1;
        }
        for (i=0; i<l2; ++i)
        {
            c = s2.charAt(i);
            if (isset(lookup, c) && (1 === lookup[c]))
            {
                ++intersection;
            }
        }
        return intersection / min(l1, l2);
    },

    ngram: function(s1, s2, n) {
        // https://en.wikipedia.org/wiki/N-gram

        var l1 = s1.length, l2 = s2.length, hits, ngram1, ngram2, t, k, p1, p2, n1, n2, i1, i2;
        if ((0 === l1) || (0 === l2)) return (0 === l1) && (0 === l2) ? 1 : 0;

        if (null == n) n = 2;
        ngram1 = this.get_ngram(s1, n);
        ngram2 = this.get_ngram(s2, n);
        if (ngram1[''] < ngram2[''])
        {
            // swap
            t = ngram1;
            ngram1 = ngram2;
            ngram2 = t;
        }

        hits = 0;
        for (k in ngram2)
        {
            if (!isset(ngram1, k) || ('' === k)) continue;
            p1 = ngram1[k];
            n1 = p1.length;
            p2 = ngram2[k];
            n2 = p2.length;
            i1 = 0;
            i2 = 0;
            while ((i1 < n1) && (i2 < n2))
            {
                if (n >= abs(p2[i2] - p1[i1])) ++hits;
                ++i1; ++i2;
            }
        }
        return 2*hits / (ngram1[''] + ngram2['']);
    },

    get_ngram: function(s, n) {
        var self = this, ngram, c, i, k;
        c = s.length - n + 1;
        ngram = {};
        for (i=0; i<c; ++i)
        {
            k = s.slice(i, i + n);
            if (!isset(ngram, k)) ngram[k] = [];
            ngram[k].push(i);
        }
        ngram[''] = c;
        return ngram;
    }
};

// utils
var stdMath = Math,
    min = stdMath.min,
    max = stdMath.max,
    abs = stdMath.abs,
    floor = stdMath.floor,
    HAS = Object.prototype.hasOwnProperty,
    toString = Object.prototype.toString;

function isset(o, x)
{
    return HAS.call(o, x);
}

// export it
return Fuzzion;
});

