<?php

namespace SS6\ShopBundle\DataFixtures\DemoMultidomain;

use Doctrine\Common\Persistence\ObjectManager;
use SS6\ShopBundle\Component\DataFixture\AbstractReferenceFixture;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupData;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade;

class PricingGroupDataFixture extends AbstractReferenceFixture {

	const ORDINARY_DOMAIN_2 = 'pricing_group_ordinary_domain_2';
	const VIP_DOMAIN_2 = 'pricing_group_vip_domain_2';

	public function load(ObjectManager $manager) {
		$pricingGroupData = new PricingGroupData();

		$pricingGroupData->name = 'Ordinary customer';
		$domainId = 2;
		$this->createPricingGroup($pricingGroupData, $domainId, self::ORDINARY_DOMAIN_2);

		$pricingGroupData->name = 'VIP customer';
		$domainId1 = 2;
		$this->createPricingGroup($pricingGroupData, $domainId1, self::VIP_DOMAIN_2);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Group\PricingGroupData $pricingGroupData
	 * @param int $domainId
	 * @param string $referenceName
	 */
	private function createPricingGroup(
		PricingGroupData $pricingGroupData,
		$domainId,
		$referenceName
	) {
		$pricingGroupFacade = $this->get(PricingGroupFacade::class);
		/* @var $pricingGroupFacade \SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade */

		$pricingGroup = $pricingGroupFacade->create($pricingGroupData, $domainId);
		$this->addReference($referenceName, $pricingGroup);
	}
}
