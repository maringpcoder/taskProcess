<?php
/**
 * Created by PhpStorm.
 * User: marin
 * Date: 2017/12/20
 * Time: 22:52
 */
class HelloWorld extends Thread {
    public $world;
    public function __construct($world) {
        $this->world = $world;
    }

    public function run() {
        print_r(sprintf("Hello %s\n", $this->world));
    }
}

$thread = new HelloWorld("World");

if ($thread->start()) {
    printf("Thread #%lu says: %s\n", $thread->getThreadId(), $thread->join());
}
new Pool();