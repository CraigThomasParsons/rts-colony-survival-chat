<?php
namespace App\Services;
use App\Models\Colonist;
/**
 * NeedsSystem
 *
 * Manages the needs (hunger, rest, mood) of a colonist.
 */
class NeedsSystem {
    protected float $hungerRate=1.0;
    protected float $restRate=0.5;
    /**
     * Update colonist needs based on time passed.
     *
     * @param Colonist $c
     * @param float $dt Time delta (default 1.0)
     * @return void
     */
    public function tick(Colonist $c, float $dt=1.0): void {
        $needs=$c->needs??[]; $h=max(0,($needs['hunger']??100)-$this->hungerRate*$dt);
        $r=max(0,($needs['rest']??100)-$this->restRate*$dt); $needs['hunger']=$h;
        $needs['rest']=$r;$needs['mood']=intval(($h+$r)/2);$c->needs=$needs;$c->save();
    }
}
// AI Notes: This service manages colonist needs.
