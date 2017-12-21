<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/20
 * Time: 22:35
 */

class MySplHeap extends SplMaxHeap
{
    /**
     * Compare elements in order to place them correctly in the heap while sifting up.
     * @link http://php.net/manual/en/splheap.compare.php
     * @param mixed $value1 <p>
     * The value of the first node being compared.
     * </p>
     * @param mixed $value2 <p>
     * The value of the second node being compared.
     * </p>
     * @return int Result of the comparison, positive integer if <i>value1</i> is greater than <i>value2</i>, 0 if they are equal, negative integer otherwise.
     * </p>
     * <p>
     * Having multiple elements with the same value in a Heap is not recommended. They will end up in an arbitrary relative position.
     * @since 5.3.0
     */
    protected function compare($value1, $value2)
    {
        // TODO: Implement compare() method.
        return ($value1 - $value2);
    }
}

$mh = new MySplHeap();
$data= array();
$vitem = [23,12,54,2,45,24,11,9,98];
foreach ($vitem as $value){
    $mh ->insert($value);
}
while(!$mh->isEmpty()){
    $data[]=$mh->extract();
}
print_r($data);

