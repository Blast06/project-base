<?php

namespace SS6\ShopBundle\Twig;

use SS6\ShopBundle\Component\Condition;
use SS6\ShopBundle\Model\Image\Config\ImageConfig;
use SS6\ShopBundle\Model\Image\Image;
use SS6\ShopBundle\Model\Image\ImageLocator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig_Extension;
use Twig_SimpleFunction;

class ImageExtension extends Twig_Extension {

	const NOIMAGE_FILENAME = 'noimage.gif';

	/**
	 * @var string
	 */
	private $imageUrlPrefix;

	/**
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	private $request;

	/**
	 * @var \SS6\ShopBundle\Model\Image\ImageLocator
	 */
	private $imageLocator;

	/**
	 * @var \SS6\ShopBundle\Model\Image\Config\ImageConfig
	 */
	private $imageConfig;

	/**
	 * @param string $imageUrlPrefix
	 * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
	 * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
	 * @param \SS6\ShopBundle\Model\Image\ImageLocator $imageLocator
	 * @param \SS6\ShopBundle\Model\Image\Config\ImageConfig $imageConfig
	 */
	public function __construct(
		$imageUrlPrefix,
		ContainerInterface $container,
		RequestStack $requestStack,
		ImageLocator $imageLocator,
		ImageConfig $imageConfig
	) {
		$this->imageUrlPrefix = $imageUrlPrefix;
		$this->container = $container; // Must inject main container - https://github.com/symfony/symfony/issues/2347
		$this->request = $requestStack->getMasterRequest();
		$this->imageLocator = $imageLocator;
		$this->imageConfig = $imageConfig;
	}

	/**
	 * Get service "templating" cannot by called in constructor - https://github.com/symfony/symfony/issues/2347
	 *
	 * @return \Symfony\Bundle\TwigBundle\Debug\TimedTwigEngine
	 */
	private function getTemplatingService() {
		return $this->container->get('templating');
	}

	/**
	 * @return array
	 */
	public function getFunctions() {
		return [
			new Twig_SimpleFunction('imageExists', [$this, 'imageExists']),
			new Twig_SimpleFunction('imageUrl', [$this, 'getImageUrl']),
			new Twig_SimpleFunction('imagesUrl', [$this, 'getImagesUrl']),
			new Twig_SimpleFunction('image', [$this, 'getImageHtml'], ['is_safe' => ['html']]),
			new Twig_SimpleFunction('imageUrlByImage', [$this, 'getImageUrlByImage']),
		];
	}

	/**
	 * @param Object $entity
	 * @param string|null $sizeName
	 * @param string|null $type
	 * @return string
	 */
	public function imageExists($entity, $sizeName = null, $type = null) {
		return $this->imageLocator->imageExistsByEntityAndType($entity, $type, $sizeName);
	}

	/**
	 * @param Object $entity
	 * @param string|null $sizeName
	 * @param string|null $type
	 * @return string
	 */
	public function getImageUrl($entity, $sizeName = null, $type = null) {
		if ($this->imageLocator->imageExistsByEntityAndType($entity, $type, $sizeName)) {
			$relativeFilepath = $this->imageLocator->getRelativeImageFilepathByEntityAndType($entity, $type, $sizeName);
		} else {
			$relativeFilepath = self::NOIMAGE_FILENAME;
		}

		$url = $this->request->getBaseUrl()
			. $this->imageUrlPrefix
			. str_replace(DIRECTORY_SEPARATOR, '/', $relativeFilepath);

		return $url;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Image\Image $image
	 * @param string|null $sizeName
	 * @return string
	 */
	public function getImageUrlByImage(Image $image, $sizeName = null) {
		if ($this->imageLocator->imageExists($image, $sizeName)) {
			$relativeFilepath = $this->imageLocator->getRelativeImageFilepathByImage($image, $sizeName);
		} else {
			$relativeFilepath = self::NOIMAGE_FILENAME;
		}

		$url = $this->request->getBaseUrl()
			. $this->imageUrlPrefix
			. str_replace(DIRECTORY_SEPARATOR, '/', $relativeFilepath);

		return $url;
	}

	/**
	 * @param Object $entity
	 * @param string|null $sizeName
	 * @param string|null $type
	 * @return array
	 */
	public function getImagesUrl($entity, $sizeName = null, $type = null) {
		$imagesUrl = [];

		$relativeFilepaths = $this->imageLocator->getRelativeImagesFilepathsByEntityAndType($entity, $type, $sizeName);
		foreach ($relativeFilepaths as $relativeFilepath) {
			$imagesUrl[] =
				$this->request->getBaseUrl()
				. $this->imageUrlPrefix
				. str_replace(DIRECTORY_SEPARATOR, '/', $relativeFilepath);
		}

		return $imagesUrl;
	}

	/**
	 * @param Object $entity
	 * @param array $attributtes
	 * @return string
	 */
	public function getImageHtml($entity, $attributtes = []) {
		Condition::setArrayDefaultValue($attributtes, 'type');
		Condition::setArrayDefaultValue($attributtes, 'size');
		Condition::setArrayDefaultValue($attributtes, 'alt', '');
		Condition::setArrayDefaultValue($attributtes, 'title', $attributtes['alt']);

		$attributtes['src'] = $this->getImageUrl($entity, $attributtes['size'], $attributtes['type']);

		$htmlAttributes = $attributtes;
		unset($htmlAttributes['type'], $htmlAttributes['size']);

		return $this->getTemplatingService()->render('@SS6Shop/Common/image.html.twig', [
			'attr' => $htmlAttributes,
			'imageCssClass' => $this->getImageEntityCssClass($entity, $attributtes['type'], $attributtes['size']),
		]);
	}

	/**
	 * @param Object $entity
	 * @param string|null $type
	 * @param string|null $sizeName
	 * @return string
	 */
	private function getImageEntityCssClass($entity, $type, $sizeName) {
		$allClassParts = [
			'image',
			$imageEntityConfig = $this->imageConfig->getEntityName($entity),
			$type,
			$sizeName,
		];
		$classParts = array_filter($allClassParts);

		return implode('-', $classParts);
	}

	/**
	 * @return string
	 */
	public function getName() {
		return 'image_extension';
	}
}
