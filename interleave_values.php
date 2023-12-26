<?php

function group_by_first_index_per_val($arr, $key) {
    $first_indices = [];

    foreach ($arr as $i => $e) {
        if (!array_key_exists($e[$key], $first_indices)) {
            $first_indices[$e[$key]] = $i;
        }
    }

    return $first_indices;
}

function group_by_values_of_key($arr, $key) {
    return array_reduce($arr, function ($a, $e) use ($key) {
        $a[$e[$key]][] = $e;
        return $a;
    }, []);
}

function inplace_sort_groups_descending(&$groups, $index_by_val, $key) {
    usort($groups, function ($a, $b) use ($index_by_val, $key) {
        $cmps = [
            count($b) - count($a),
            $index_by_val[$a[0][$key]] - $index_by_val[$b[0][$key]],
        ];

        foreach ($cmps as $diff) {
            if ($diff !== 0) {
                return $diff;
            }
        }

        return $b <=> $a;
    });
}

function interleave_from_groups($groups) {
    $zipped = array_map(null, ...$groups);
    return array_reduce($zipped, function ($acc, $group) {
        foreach ($group as $e) {
            if (!$e) {
                break;
            }

            $acc[] = $e;
        }

        return $acc;
    }, []);
}

function interleave_values($arr, $key) {
    if (empty($arr)) {
        return [];
    }

    $groups = group_by_values_of_key($arr, $key);
    $first_indices = group_by_first_index_per_val($arr, $key);
    inplace_sort_groups_descending($groups, $first_indices, $key);
    return interleave_from_groups($groups);
}



$data = [
    ["k" => 1],
    ["k" => 3],
    ["k" => 2],
    ["k" => 1],
    ["k" => 3],
    ["k" => 3],
];
$data2 = [
    ["id" => 1, "qty" => 22],
    ["id" => 2, "qty" => 33],
    ["id" => 3, "qty" => 11],
    ["id" => 4, "qty" => 11],
    ["id" => 5, "qty" => 33],
];
function test($data, $name)
{
    $d = interleave_values($data, $name);
    foreach ($d as $value) {
        foreach ($value as $k => $v) {
            echo "[$k] => $v\n";
        }
    }
}
function test2($data, $name)
{

    $d = interleave_values($data, $name);
    $i = 0;
    $str = "";
    foreach ($d as $value) {
        $str .= "[";
        $arr = [];
        foreach ($value as $k => $v) {
            $arr[] = "$k => $v";
        }
        $str .= implode(', ', $arr);
        $str .= "]\n";
        $i++;
    }
    echo $str;
}

test($data, 'k');
echo "----\n";
test2($data2, 'qty');
