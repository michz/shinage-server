<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  12.02.17
 * @time     :  17:08
 */

namespace AppBundle\Entity;

class QuotaCalculated
{
    protected $usedAbs = 0;
    protected $totalAbs = 0;
    protected $freeAbs = 0;

    public function setUsedAbsolute($used)
    {
        $this->usedAbs = $used;
        return $this;
    }
    public function setTotalAbsolute($total)
    {
        $this->totalAbs = $total;
        return $this;
    }
    public function setFreeAbsolute($free)
    {
        $this->freeAbs = $free;
        return $this;
    }

    public function getUsedAbsolute()
    {
        return $this->usedAbs;
    }
    public function getFreeAbsolute()
    {
        return $this->freeAbs;
    }
    public function getTotalAbsolute()
    {
        return $this->totalAbs;
    }

    public function getUsedPercent()
    {
        if ($this->totalAbs == 0) {
            return 0;
        }
        return ($this->usedAbs / $this->totalAbs);
    }
    public function getFreePercent()
    {
        if ($this->totalAbs == 0) {
            return 1;
        }
        return ($this->freeAbs / $this->totalAbs);
    }
}
