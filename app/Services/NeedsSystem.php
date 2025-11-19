<?php
namespace App\Services;
use App\Models\Colonist;

/** NeedsSystem (same as earlier) */
class NeedsSystem { protected $hungerRate=1.0; protected $restRate=0.5; public function tick(Colonist $c, float $dt=1.0): void { $needs=$c->needs??[]; $h=max(0,($needs['hunger']??100)-$this->hungerRate*$dt); $r=max(0,($needs['rest']??100)-$this->restRate*$dt); $needs['hunger']=$h;$needs['rest']=$r;$needs['mood']=intval(($h+$r)/2);$c->needs=$needs;$c->save(); } }
