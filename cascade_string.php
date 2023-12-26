<?php

function transform_string($s, $gap): string {
    if(empty($s)) {
        return "\n";
    }
    if(empty($gap)) {
        $gap = 1;
    }

    $words = explode(' ', $s);
    $max_length = max(array_map('strlen', $words));
    $underscore = "_";

    $arr = [];
    for ($i = 0; $i < $max_length; $i++) {
        $result = str_repeat($underscore, $i);
        for ($j = 0; $j < count($words); $j++) {
            if ($i < strlen($words[$j])) {
                $result .= $words[$j][$i];
            } else {
                $result .= $underscore;
            }
            $result .= str_repeat($underscore, $gap);
        }
        $arr[] = rtrim($result, '_');
    }

    $equalizeStringLengths = function ($array) {
        $maxLength = max(array_map('strlen', $array));
        foreach ($array as &$string) {
            $string .= str_repeat('_', $maxLength - strlen($string));
        }
        return $array;
    };
    return implode("\n",$equalizeStringLengths($arr))."\n";
}

// Testando a função
echo transform_string('', 2);
echo "#############\n";
echo transform_string('AB', 0);
echo "#############\n";
echo transform_string('AB', 2);
echo "#############\n";
echo transform_string('ABC', 2);
echo "#############\n";
echo transform_string('ABC DEF', 0);
echo "#############\n";
echo transform_string('ABC DEF', 2);
echo "#############\n";
echo transform_string('The codings bug', 2);
echo "#############\n";
echo transform_string('There is no one who loves pain itself, who seeks after it and wants to have it, simply because it is pain', 3);
echo "#############\n";