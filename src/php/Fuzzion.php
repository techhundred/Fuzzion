<?php
/**
*  Fuzzion
*  Fuzzy / approximate string matching metrics for PHP, JavaScript, Python
*
*  @version: 1.0.0
*  https://github.com/foo123/Fuzzion
*
**/
if (!class_exists('Fuzzion', false))
{
/**
References:

Navarro, Gonzalo (March 2001), "A guided tour to approximate string matching"
https://www.thefreelibrary.com/A+Guided+Tour+to+Approximate+String+Matching.-a075950731

Wikipedia contributors. (2024, August 12). String metric. In Wikipedia, The Free Encyclopedia. Retrieved 19:36, September 26, 2025, from https://en.wikipedia.org/w/index.php?title=String_metric&oldid=1239983587
*/
class Fuzzion
{
    const VERSION = "1.0.0";

    //protected $mbmap = null;
    //protected $ascii = null;

    public function __construct()
    {
        // caches
        //$this->mbmap = array();
        //$this->ascii = array();
    }

    public function ident($s1, $s2)
    {
        return $s1 === $s2 ? 1 : 0;
    }

    public function levenshtein($s1, $s2/*, $transpositions = false*/)
    {
        // https://en.wikipedia.org/wiki/Levenshtein_distance
        // https://en.wikipedia.org/wiki/Damerau%E2%80%93Levenshtein_distance
        // allows only insertions, deletions and substitutions (and optionally adjacent transpositions)

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        if ($l2 > $l1)
        {
            // swap
            $t = $s1;
            $s1 = $s2;
            $s2 = $t;
            $t = $l1;
            $l1 = $l2;
            $l2 = $t;
        }
        $b = array_fill(0, $l2+1, 0);
        $a = array_fill(0, $l2+1, 0);
        for ($j=0; $j<=$l2; ++$j) $a[$j] = $j;

        for ($i=1; $i<=$l1; ++$i)
        {
            // swap
            $t = &$a;
            $a = &$b;
            $b = &$t;
            $a[0] = $b[0] = $i - 1;
            $c1 = mb_substr($s1, $i-1, 1, 'UTF-8');
            for ($j=1; $j<=$l2; ++$j)
            {
                $c2 = mb_substr($s2, $j-1, 1, 'UTF-8');
                $d = $c1 === $c2 ? 0 : 1;
                $a[$j] = min(
                    $b[$j  ] +  1,        // deletion
                    $a[$j-1] +  1,        // insertion
                    $b[$j-1] +  $d        // substitution
                );
                /*if ($transpositions && ($i > 1) && ($j > 1) && (mb_substr($s1, $i-1, 1, 'UTF-8') === mb_substr($s2, $j-2, 1, 'UTF-8')) && (mb_substr($s1, $i-2, 1, 'UTF-8') === mb_substr($s2, $j-1, 1, 'UTF-8')))
                {
                    $a[$j] = min(
                        $a[$j  ],
                        $c[$j-2] + $d     // transposition
                    );
                }*/
            }
        }
        return 1 - $a[$l2] / $l1;
    }

    public function lcs($s1, $s2)
    {
        // https://en.wikipedia.org/wiki/Longest_common_subsequence_problem
        // allows only insertions and deletions

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        if ($l2 > $l1)
        {
            // swap
            $t = $s1;
            $s1 = $s2;
            $s2 = $t;
            $t = $l1;
            $l1 = $l2;
            $l2 = $t;
        }
        $b = array_fill(0, $l2, 0);
        $a = array_fill(0, $l2, 0);
        for ($i=0; $i<$l1; ++$i)
        {
            // swap
            $t = &$a;
            $a = &$b;
            $b = &$t;
            $c1 = mb_substr($s1, $i, 1, 'UTF-8');
            for ($j=0; $j<$l2; ++$j)
            {
                $c2 = mb_substr($s2, $j, 1, 'UTF-8');
                if ($c1 === $c2)
                {
                    $a[$j] = (0 === $i || 0 === $j ? 0 : $b[$j-1]) + 1;
                }
                else
                {
                    $a[$j] = max(0 === $j ? 0 : $a[$j-1], 0 === $i ? 0 : $b[$j]);
                }
            }
        }
        return 1 - ($l1 + $l2 - 2*$a[$l2-1]) / $l1;
    }

    public function hamming($s1, $s2)
    {
        // https://en.wikipedia.org/wiki/Hamming_distance
        // allows only substitutions

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        $lm = min($l1, $l2);
        $lM = max($l1, $l2);
        $d = $lM - $lm;
        for ($i=0; $i<$lm; ++$i)
        {
            if (mb_substr($s1, $i, 1, 'UTF-8') !== mb_substr($s2, $i, 1, 'UTF-8'))
            {
                ++$d;
            }
        }
        return 1 - $d / $lM;
    }

    public function jaro($s1, $s2)
    {
        // https://en.wikipedia.org/wiki/Jaro%E2%80%93Winkler_distance

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        // jaro distance
        // max distance between two chars to be considered matching
        $match_distance = max($l1, $l2) / 2 - 1;
        $s1_matches = array_fill(0, $l1, false);
        $s2_matches = array_fill(0, $l2, false);

        // number of matches and transpositions
        $matches        = 0;
        $transpositions = 0;

        // find the matches
        for ($i=0; $i<$l1; ++$i)
        {
            // start and end take into account the match distance
            $start = (int)max(0, $i - $match_distance);
            $end   = (int)min($i + $match_distance + 1, $l2);

            for ($k=$start; $k<$end; ++$k)
            {
                // if str2 already has a match continue
                if ($s2_matches[$k]) continue;

                // if str1 and str2 are not
                if (mb_substr($s1, $i, 1, 'UTF-8') !== mb_substr($s2, $k, 1, 'UTF-8')) continue;

                // otherwise assume there is a match
                $s1_matches[$i] = true;
                $s2_matches[$k] = true;
                ++$matches;
                break;
            }
        }

        if (0 === $matches)
        {
            // if there are no matches return 0
            $j = 0;
        }
        else
        {
            // count transpositions
            $k = 0;
            for ($i=0; $i<$l1; ++$i)
            {
                // if there are no matches in str1 continue
                if (!$s1_matches[$i]) continue;

                // while there is no match in str2 increment k
                while (!$s2_matches[$k]) ++$k;

                // increment transpositions
                if (mb_substr($s1, $i, 1, 'UTF-8') !== mb_substr($s2, $k, 1, 'UTF-8')) ++$transpositions;

                ++$k;
            }

            // divide the number of transpositions by two as per the algorithm specs
            // this division is valid because the counted transpositions include both
            // instances of the transposed characters.
            $transpositions /= 2.0;

            // return the Jaro distance
            $j = (($matches / $l1) + ($matches / $l2) + (($matches - $transpositions) / $matches)) / 3;
        }

        // jarowinkler distance
        if ($j < /*$thres*/0.5)
        {
            $jw = $j;
        }
        else
        {
            $lengthOfCommonPrefix = 0;
            $l = min($l1, $l2);
            for ($i=0; $i<$l; ++$i)
            {
                if (mb_substr($s1, $i, 1, 'UTF-8') === mb_substr($s2, $i, 1, 'UTF-8')) ++$lengthOfCommonPrefix;
                else break;
            }
            $lp = min(0.1, 1.0 / max($l1, $l2)) * $lengthOfCommonPrefix;
            $jw = $lp + $j * (1 - $lp);
        }
        return $jw;
    }

    public function jaccard($s1, $s2)
    {
        // https://en.wikipedia.org/wiki/Jaccard_index

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        $lookup = array();
        $intersection = 0;
        $union = 0;
        for ($i=0; $i<$l1; ++$i)
        {
            $c = mb_substr($s1, $i, 1, 'UTF-8');
            if (isset($lookup[$c])) continue;
            $lookup[$c] = 1;
            ++$union;
        }
        for ($i=0; $i<$l2; ++$i)
        {
            $c = mb_substr($s2, $i, 1, 'UTF-8');
            if (isset($lookup[$c]))
            {
                ++$intersection;
            }
            else
            {
                ++$union;
            }
        }
        return $intersection / $union;
    }

    public function overlap($s1, $s2)
    {
        // https://en.wikipedia.org/wiki/Overlap_coefficient

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        $lookup = array();
        $intersection = 0;
        for ($i=0; $i<$l1; ++$i)
        {
            $c = mb_substr($s1, $i, 1, 'UTF-8');
            if (isset($lookup[$c])) continue;
            $lookup[$c] = 1;
        }
        for ($i=0; $i<$l2; ++$i)
        {
            $c = mb_substr($s2, $i, 1, 'UTF-8');
            if (isset($lookup[$c]))
            {
                ++$intersection;
            }
        }
        return $intersection / min($l1, $l2);
    }

    public function ngram($s1, $s2, $n = 2)
    {
        // https://en.wikipedia.org/wiki/N-gram

        //$s1 = $this->asciify($s1);
        //$s2 = $this->asciify($s2);

        $l1 = mb_strlen($s1, 'UTF-8');
        $l2 = mb_strlen($s2, 'UTF-8');
        if ((0 === $l1) || (0 === $l2)) return (0 === $l1) && (0 === $l2) ? 1 : 0;

        $ngram1 = $this->get_ngram($s1, $n);
        $ngram2 = $this->get_ngram($s2, $n);
        if ($ngram1[''] < $ngram2[''])
        {
            // swap
            $t = $ngram1;
            $ngram1 = $ngram2;
            $ngram2 = $t;
        }

        $hits = 0;
        foreach (array_keys($ngram2) as $k)
        {
            if (('' === $k) || !isset($ngram1[$k])) continue;
            $p1 = $ngram1[$k];
            $n1 = count($p1);
            $p2 = $ngram2[$k];
            $n2 = count($p2);
            $i1 = 0;
            $i2 = 0;
            while (($i1 < $n1) && ($i2 < $n2))
            {
                if ($n >= abs($p2[$i2] - $p1[$i1])) ++$hits;
                ++$i1; ++$i2;
            }
        }
        return 2*$hits / ($ngram1[''] + $ngram2['']);
    }

    public function get_ngram($s, $n)
    {
        $c = mb_strlen($s, 'UTF-8') - $n + 1;
        $ngram = array();
        for ($i=0; $i<$c; ++$i)
        {
            $k = mb_substr($s, $i, $n, 'UTF-8');
            if (!isset($ngram[$k])) $ngram[$k] = array();
            $ngram[$k][] = $i;
        }
        $ngram[''] = $c;
        return $ngram;
    }

    /*public function asciify($string)
    {
        // remap a multi-byte utf8 string to ascii character set
        // so that simpler and faster string comparisons can be used later
        $ret = $string;
        if (isset($this->ascii[$string]))
        {
            $ret = $this->ascii[$string];
        }
        else
        {
            // find all utf8 characters
            if (preg_match_all('/[\xC0-\xF7][\x80-\xBF]+/', $string, $matches))
            {
                // update the encoding map with the characters not already met
                // this works correctly if number of utf8 chars in language alphabet is less then 128
                // which is true for most language character sets
                $mapCount = count($this->mbmap);
                foreach ($matches[0] as $mbc)
                {
                    if (!isset($this->mbmap[$mbc]))
                    {
                        $this->mbmap[$mbc] = chr(128 + $mapCount);
                        ++$mapCount;
                    }
                }
                // remap any utf8 characters to ascii
                $ret = strtr($string, $this->mbmap);
            }
            $this->ascii[$string] = $ret;
        }
        return $ret;
    }*/
}
}
