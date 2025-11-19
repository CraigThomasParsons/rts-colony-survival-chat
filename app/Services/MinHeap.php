<?php
namespace App\Services;

/**
 * Simple binary MinHeap implementation for A* open set.
 */
class MinHeap
{
    protected $heap = [];

    public function isEmpty() { return empty($this->heap); }

    public function insert($key, $priority)
    {
        $this->heap[] = ['key'=>$key,'priority'=>$priority];
        $this->siftUp(count($this->heap)-1);
    }

    public function extract()
    {
        if ($this->isEmpty()) return null;
        $root = $this->heap[0]['key'];
        $last = array_pop($this->heap);
        if (!empty($this->heap)) {
            $this->heap[0] = $last;
            $this->siftDown(0);
        }
        return $root;
    }

    protected function siftUp($i)
    {
        while ($i > 0) {
            $p = intdiv($i-1,2);
            if ($this->heap[$i]['priority'] < $this->heap[$p]['priority']) {
                $tmp = $this->heap[$p]; $this->heap[$p] = $this->heap[$i]; $this->heap[$i] = $tmp;
                $i = $p;
            } else break;
        }
    }

    protected function siftDown($i)
    {
        $n = count($this->heap);
        while (true) {
            $l = 2*$i+1; $r = 2*$i+2; $small = $i;
            if ($l<$n && $this->heap[$l]['priority'] < $this->heap[$small]['priority']) $small = $l;
            if ($r<$n && $this->heap[$r]['priority'] < $this->heap[$small]['priority']) $small = $r;
            if ($small !== $i) { $tmp = $this->heap[$small]; $this->heap[$small] = $this->heap[$i]; $this->heap[$i] = $tmp; $i=$small; } else break;
        }
    }
}
