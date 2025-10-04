# -*- coding: utf-8 -*-
import os, sys

DIR = os.path.dirname(os.path.abspath(__file__))

def import_module(name, path):
    #import imp
    #try:
    #    mod_fp, mod_path, mod_desc  = imp.find_module(name, [path])
    #    mod = getattr( imp.load_module(name, mod_fp, mod_path, mod_desc), name )
    #except ImportError as exc:
    #    mod = None
    #    sys.stderr.write("Error: failed to import module ({})".format(exc))
    #finally:
    #    if mod_fp: mod_fp.close()
    #return mod
    import importlib.util, sys
    spec = importlib.util.spec_from_file_location(name, path+name+'.py')
    mod = importlib.util.module_from_spec(spec)
    sys.modules[name] = mod
    spec.loader.exec_module(mod)
    return getattr(mod, name)

# import the Fuzzion.py (as a) module, probably you will want to place this in another dir/package
Fuzzion = import_module('Fuzzion', os.path.join(DIR, '../../src/py/'))
if not Fuzzion:
    print ('Could not load the Fuzzion Module')
    sys.exit(1)

def test():
    matcher = Fuzzion()
    tests = [
    ['jointure', 'join'],
    ['jointure', 'jiontre'],
    ['jointure', 'joitnrue'],
    ['jointure', 'turejoin']
    ]

    for test in tests:
        string1 = test[0]
        string2 = test[1]
        print()
        print('levenshtein("'+string1+'", "'+string2+'") = '+str(matcher.levenshtein(string1, string2)))
        print('damerau("'+string1+'", "'+string2+'") = '+str(matcher.damerau(string1, string2)))
        print('lcs("'+string1+'", "'+string2+'") = '+str(matcher.lcs(string1, string2)))
        print('jaccard("'+string1+'", "'+string2+'") = '+str(matcher.jaccard(string1, string2)))
        print('overlap("'+string1+'", "'+string2+'") = '+str(matcher.overlap(string1, string2)))
        print('hamming("'+string1+'", "'+string2+'") = '+str(matcher.hamming(string1, string2)))
        print('jaro("'+string1+'", "'+string2+'") = '+str(matcher.jaro(string1, string2)))
        print('ngram("'+string1+'", "'+string2+'", 2) = '+str(matcher.ngram(string1, string2, 2)))
        print('ngram("'+string1+'", "'+string2+'", 3) = '+str(matcher.ngram(string1, string2, 3)))

test()