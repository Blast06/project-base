<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductTest extends GraphQlTestCase
{
    /**
     * @var \App\Model\Product\Product
     */
    private $product;

    protected function setUp(): void
    {
        $productFacade = $this->getContainer()->get(ProductFacade::class);

        /** @var \App\Model\Product\Product $product */
        $product = $productFacade->getById(1);
        $this->product = $product;

        parent::setUp();
    }

    public function testProductDetailNameByUuid(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    name
                }
            }
        ';

        $arrayExpected = [
            'data' => [
                'product' => [
                    'name' => t(
                        '22" Sencor SLE 22F46DM4 HELLO KITTY',
                        [],
                        'dataFixtures',
                        $this->getLocaleForFirstDomain()
                    ),
                ],
            ],
        ];

        $this->assertQueryWithExpectedArray($query, $arrayExpected);
    }

    public function testProductDetailWithAllAttributesByUuid(): void
    {
        $query = '
            query {
                product(uuid: "' . $this->product->getUuid() . '") {
                    name,
                    shortDescription,
                    link,
                    unit {
                        name
                    },
                    availability{
                        name
                    },
                    stockQuantity,
                    categories {
                        name
                    },
                    flags {
                        name, 
                        rgbColor
                    },
                    price {
                        priceWithVat,
                        priceWithoutVat,
                        vatAmount
                    },
                    brand {
                        name
                    },
                    accessories {
                        name
                    },
                    isSellingDenied,
                    description,
                    orderingPriority
                    parameters {
                        name
                        values {
                            text
                        }
                    }
                }
            }
        ';

        $this->assertQueryWithExpectedArray($query, $this->getExpectedProductDetailWithAllAttributes());
    }

    /**
     * @return array
     */
    private function getExpectedProductDetailWithAllAttributes(): array
    {
        $firstDomainLocale = $this->getLocaleForFirstDomain();
        $shortDescription = t(
            'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback',
            [],
            'dataFixtures',
            $firstDomainLocale
        );

        return [
            'data' => [
                'product' => [
                    'name' => t('22" Sencor SLE 22F46DM4 HELLO KITTY', [], 'dataFixtures', $firstDomainLocale),
                    'shortDescription' => $shortDescription,
                    'link' => $this->getLocalizedPathOnFirstDomainByRouteName('front_product_detail', ['id' => 1]),
                    'unit' => [
                        'name' => t('pcs', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'availability' => [
                        'name' => t('In stock', [], 'dataFixtures', $firstDomainLocale),
                    ],
                    'stockQuantity' => 300,
                    'categories' => [
                        [
                            'name' => t('Electronics', [], 'dataFixtures', $firstDomainLocale),
                        ],
                        [
                            'name' => t('TV, audio', [], 'dataFixtures', $firstDomainLocale),
                        ],
                    ],
                    'flags' => [
                        [
                            'name' => t('TOP', [], 'dataFixtures', $firstDomainLocale),
                            'rgbColor' => '#d6fffa',
                        ],
                        [
                            'name' => t('Action', [], 'dataFixtures', $firstDomainLocale),
                            'rgbColor' => '#f9ffd6',
                        ],
                    ],
                    'price' => [
                        'priceWithVat' => $this->getPriceWithVatConvertedToDomainDefaultCurrency('3499'),
                        'priceWithoutVat' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('2891.74'),
                        'vatAmount' => $this->getPriceWithoutVatConvertedToDomainDefaultCurrency('607.26'),
                    ],
                    'brand' => [
                        'name' => 'Sencor',
                    ],
                    'accessories' => [
                        [
                            'name' => t(
                                'Kabel HDMI A - HDMI A M/M 2m gold-plated connectors High Speed HD',
                                [],
                                'dataFixtures',
                                $firstDomainLocale
                            ),
                        ],
                        [
                            'name' => t('Defender 2.0 SPK-480', [], 'dataFixtures', $firstDomainLocale),
                        ],
                    ],
                    'isSellingDenied' => false,
                    'description' => t(
                        'Television LED, 55 cm diagonal, 1920x1080 Full HD, DVB-T MPEG4 tuner with USB recording and playback (DivX, XviD, MP3, WMA, JPEG), HDMI, SCART, VGA, pink execution, energ. Class B',
                        [],
                        'dataFixtures',
                        $firstDomainLocale
                    ),
                    'orderingPriority' => 1,
                    'parameters' => [
                        [
                            'name' => t('HDMI', [], 'dataFixtures', $firstDomainLocale),
                            'values' => [
                                [
                                    'text' => t('Yes', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Resolution', [], 'dataFixtures', $firstDomainLocale),
                            'values' => [
                                [
                                    'text' => t('1920×1080 (Full HD)', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Screen size', [], 'dataFixtures', $firstDomainLocale),
                            'values' => [
                                [
                                    'text' => t('27"', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('Technology', [], 'dataFixtures', $firstDomainLocale),
                            'values' => [
                                [
                                    'text' => t('LED', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                        [
                            'name' => t('USB', [], 'dataFixtures', $firstDomainLocale),
                            'values' => [
                                [
                                    'text' => t('Yes', [], 'dataFixtures', $firstDomainLocale),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
