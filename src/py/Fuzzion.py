##
#  Fuzzion
#  Fuzzy / approximate string similarity metrics for PHP, JavaScript, Python
#
#  @version: 1.0.0
#  https://github.com/foo123/Fuzzion
#
##
# -*- coding: utf-8 -*-

##
#References:
#
#Navarro, Gonzalo (March 2001), "A guided tour to approximate string matching"
#https://www.thefreelibrary.com/A+Guided+Tour+to+Approximate+String+Matching.-a075950731
#
#Wikipedia contributors. (2024, August 12). String metric. In Wikipedia, The Free Encyclopedia. Retrieved 19:36, September 26, 2025, from https://en.wikipedia.org/w/index.php?title=String_metric&oldid=1239983587
##
class Fuzzion:
    """
    Fuzzion
    Fuzzy / approximate string matching metrics for PHP, JavaScript, Python
    @version: 1.0.0
    https://github.com/foo123/Fuzzion
    """

    VERSION = "1.0.0"

    def __init__(self):
        pass

    def ident(self, s1, s2):
        return 1 if s1 == s2 else 0

    def levenshtein(self, s1, s2):
        # https://en.wikipedia.org/wiki/Levenshtein_distance
        # counts only insertions, deletions and substitutions

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        if l2 > l1:
            # swap
            t = s1
            s1 = s2
            s2 = t
            t = l1
            l1 = l2
            l2 = t
        b = [0] * (l2+1)
        a = [0] * (l2+1)
        for j in range(l2+1): a[j] = j

        for i in range(1, l1+1):
            # swap
            t = a
            a = b
            b = t
            a[0] = b[0] = i - 1
            c1 = s1[i-1]
            for j in range(1, l2+1):
                c2 = s2[j-1]
                d = 0 if c1 == c2 else 1
                a[j] = min(
                    b[j  ] +  1,        # deletion
                    a[j-1] +  1,        # insertion
                    b[j-1] +  d         # substitution
                )
        return 1 - a[l2] / l1

    def damerau(self, s1, s2):
        # https://en.wikipedia.org/wiki/Damerau%E2%80%93Levenshtein_distance
        # counts only insertions, deletions, substitutions and adjacent transpositions

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        if l2 > l1:
            # swap
            t = s1
            s1 = s2
            s2 = t
            t = l1
            l1 = l2
            l2 = t
        b = [0] * (l2+1)
        a = [0] * (l2+1)
        for j in range(l2+1): a[j] = j

        for i in range(1, l1+1):
            # swap
            c = b[:]
            t = a
            a = b
            b = t
            a[0] = b[0] = i - 1
            c1 = s1[i-1]
            for j in range(1, l2+1):
                c2 = s2[j-1]
                d = 0 if c1 == c2 else 1
                a[j] = min(
                    b[j  ] +  1,        # deletion
                    a[j-1] +  1,        # insertion
                    b[j-1] +  d         # substitution
                )
                if (i > 1) and (j > 1) and (s1[i-1] == s2[j-2]) and (s1[i-2] == s2[j-1]):
                    a[j] = min(
                        a[j  ],
                        c[j-2] + d     # transposition
                    )
        return 1 - a[l2] / l1

    def lcs(self, s1, s2):
        # https://en.wikipedia.org/wiki/Longest_common_subsequence_problem
        # counts only insertions and deletions

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        if l2 > l1:
            # swap
            t = s1
            s1 = s2
            s2 = t
            t = l1
            l1 = l2
            l2 = t
        b = [0] * l2
        a = [0] * l2
        for i in range(l1):
            # swap
            t = a
            a = b
            b = t
            c1 = s1[i]
            for j in range(l2):
                c2 = s2[j]
                if c1 == c2:
                    a[j] = (0 if 0 == i or 0 == j else b[j-1]) + 1
                else:
                    a[j] = max(0 if 0 == j else a[j-1], 0 if 0 == i else b[j])
        return 1 - (l1 + l2 - 2*a[l2-1]) / l1

    def hamming(self, s1, s2):
        # https://en.wikipedia.org/wiki/Hamming_distance
        # counts only substitutions and either deletions or insertions

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        lm = min(l1, l2)
        lM = max(l1, l2)
        d = lM - lm
        for i in range(lm):
            if s1[i] != s2[i]:
                d += 1
        return 1 - d / lM

    def jaro(self, s1, s2):
        # https://en.wikipedia.org/wiki/Jaro%E2%80%93Winkler_distance

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        # jaro distance
        # max distance between two chars to be considered matching
        match_distance = max(l1, l2) / 2 - 1
        s1_matches = [False] * l1
        s2_matches = [False] * l2

        # number of matches and transpositions
        matches        = 0
        transpositions = 0

        # find the matches
        for i in range(l1):
            # start and end take into account the match distance
            start = int(max(0, i - match_distance))
            end   = int(min(i + match_distance + 1, l2))

            for k in range(start, end):
                # if str2 already has a match continue
                if s2_matches[k]: continue

                # if str1 and str2 are not
                if s1[i] != s2[k]: continue

                # otherwise assume there is a match
                s1_matches[i] = True
                s2_matches[k] = True
                matches += 1
                break

        if 0 == matches:
            # if there are no matches return 0
            j = 0
        else:
            # count transpositions
            k = 0
            for i in range(l1):
                # if there are no matches in str1 continue
                if not s1_matches[i]: continue

                # while there is no match in str2 increment k
                while not s2_matches[k]: k += 1

                # increment transpositions
                if s1[i] != s2[k]: transpositions += 1

                k += 1

            # divide the number of transpositions by two as per the algorithm specs
            # this division is valid because the counted transpositions include both
            # instances of the transposed characters.
            transpositions /= 2.0

            # return the Jaro distance
            j = ((matches / l1) + (matches / l2) + ((matches - transpositions) / matches)) / 3

        # jarowinkler distance
        if j < 0.5: #thres
            jw = j
        else:
            lengthOfCommonPrefix = 0
            l = min(l1, l2)
            for i in range(l):
                if s1[i] == s2[i]: lengthOfCommonPrefix += 1
                else: break
            lp = min(0.1, 1.0 / max(l1, l2)) * lengthOfCommonPrefix
            jw = lp + j * (1 - lp)
        return jw

    def jaccard(self, s1, s2):
        # https://en.wikipedia.org/wiki/Jaccard_index

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        lookup = {}
        intersection = 0
        union = 0
        for i in range(l1):
            c = s1[i]
            if c in lookup: continue
            lookup[c] = 1
            union += 1
        for i in range(l2):
            c = s2[i]
            if c in lookup:
                intersection += 1
            else:
                union += 1
        return intersection / union

    def overlap(self, s1, s2):
        # https://en.wikipedia.org/wiki/Overlap_coefficient

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        lookup = {}
        intersection = 0
        for i in range(l1):
            c = s1[i]
            if c in lookup: continue
            lookup[c] = 1
        for i in range(l2):
            c = s2[i]
            if c in lookup:
                intersection += 1
        return intersection / min(l1, l2)

    def ngram(self, s1, s2, n = 2):
        # https://en.wikipedia.org/wiki/N-gram

        l1 = len(s1)
        l2 = len(s2)
        if (0 == l1) or (0 == l2): return 1 if (0 == l1) and (0 == l2) else 0

        ngram1 = self.get_ngram(s1, n)
        ngram2 = self.get_ngram(s2, n)
        if ngram1[''] < ngram2['']:
            # swap
            t = ngram1
            ngram1 = ngram2
            ngram2 = t

        hits = 0
        for k in ngram2:
            if (k not in ngram1) or ('' == k): continue
            p1 = ngram1[k]
            n1 = len(p1)
            p2 = ngram2[k]
            n2 = len(p2)
            i1 = 0
            i2 = 0
            while (i1 < n1) and (i2 < n2):
                if n >= abs(p2[i2] - p1[i1]): hits += 1
                i1 += 1
                i2 += 1

        return 2*hits / (ngram1[''] + ngram2[''])

    def get_ngram(self, s, n):
        c = len(s) - n + 1
        ngram = {}
        for i in range(c):
            k = s[i:i + n]
            if k not in ngram: ngram[k] = []
            ngram[k].append(i)
        ngram[''] = c
        return ngram


__all__ = ['Fuzzion']

