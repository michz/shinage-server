<?php
/**
 * @author   :  Michael Zapf <m.zapf@mztx.de>
 * @date     :  03.02.17
 * @time     :  18:45
 */

namespace AppBundle\Entity\Interfaces;

use AppBundle\Entity\Organization;
use AppBundle\Entity\User;

interface Ownable
{
    public function setOwnerUser(User $user);
    public function setOwnerOrga(Organization $orga);

    public function getOwnerUser();
    public function getOwnerOrga();


    public function getOwnerString();
    public function setOwnerString($str);
}
