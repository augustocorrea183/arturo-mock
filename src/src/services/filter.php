<?php
/**
 * Created by PhpStorm.
 * User: acorrea
 * Date: 30/09/19
 * Time: 13:35
 */

namespace Services;

class Filter
{
    private $field;

    public function setField($field) {
        $this->field = $field;
    }

    public function applyFilterRules($rules, $data) {

        $this->setField($rules['_sortField']);

        // Sort
        if($rules['_sortDir'] === 'DESC') {
            usort($data, function($a, $b) {
                return $a[$this->field] < $b[$this->field];
            });
        } elseif($rules['_sortDir'] === 'ASC') {
            usort($data, function($a, $b) {
                return $a[$this->field] > $b[$this->field];
            });
        }

        // Filter
        if(isset($rules['_filters'])) {
            $filter = json_decode($rules['_filters'], true);
            $filterKey = key($filter);
            $filterValue = $filter[key($filter)];
            $result = [];
            foreach ($data as $mock) {
                foreach ($mock as $key => $field) {
                    if($key == $filterKey && strpos($field, $filterValue) !== false) {
                        $result[] = $mock;
                    }
                }
            }

            $data = $result;
        }

        // Pag
        $page = $rules['_page'];
        $perPage = $rules['_perPage'];
        $final = $page * $perPage;
        $initial = $final - $perPage;
        $paginated = [];

        for($x=$initial ; $x<=$final ; $x++) {
            if(isset($data[$x])) {
                $paginated[] = $data[$x];
            }
        }



        return $paginated;

    }

}