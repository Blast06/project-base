<?php

declare(strict_types=1);

namespace App\Controller\Front;

use Shopsys\FrameworkBundle\Model\Advert\AdvertFacade;

class AdvertController extends FrontBaseController
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Advert\AdvertFacade
     */
    private $advertFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertFacade $advertFacade
     */
    public function __construct(AdvertFacade $advertFacade)
    {
        $this->advertFacade = $advertFacade;
    }

    /**
     * @param string $positionName
     */
    public function boxAction($positionName)
    {
        $advert = $this->advertFacade->findRandomAdvertByPositionOnCurrentDomain($positionName);

        return $this->render('Front/Content/Advert/box.html.twig', [
            'advert' => $advert,
        ]);
    }
}
