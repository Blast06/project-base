<?php

namespace SS6\ShopBundle\Controller\Admin;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use SS6\ShopBundle\Component\Controller\AdminBaseController;
use SS6\ShopBundle\Component\Router\Security\Annotation\CsrfProtection;
use SS6\ShopBundle\Component\Translation\Translator;
use SS6\ShopBundle\Form\Admin\Product\ProductEditFormTypeFactory;
use SS6\ShopBundle\Form\Admin\Product\ProductMassActionFormType;
use SS6\ShopBundle\Form\Admin\Product\VariantFormType;
use SS6\ShopBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use SS6\ShopBundle\Form\Admin\QuickSearch\QuickSearchFormType;
use SS6\ShopBundle\Model\Administrator\AdministratorGridFacade;
use SS6\ShopBundle\Model\AdminNavigation\Breadcrumb;
use SS6\ShopBundle\Model\AdminNavigation\MenuItem;
use SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFacade;
use SS6\ShopBundle\Model\Category\CategoryFacade;
use SS6\ShopBundle\Model\Grid\GridFactory;
use SS6\ShopBundle\Model\Grid\QueryBuilderWithRowManipulatorDataSource;
use SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade;
use SS6\ShopBundle\Model\Product\Detail\ProductDetailFactory;
use SS6\ShopBundle\Model\Product\Listing\ProductListAdminFacade;
use SS6\ShopBundle\Model\Product\MassAction\ProductMassActionFacade;
use SS6\ShopBundle\Model\Product\ProductEditDataFactory;
use SS6\ShopBundle\Model\Product\ProductEditFacade;
use SS6\ShopBundle\Model\Product\ProductVariantFacade;
use SS6\ShopBundle\Twig\ProductExtension;
use Symfony\Component\HttpFoundation\Request;

class ProductController extends AdminBaseController {

	/**
	 * @var \SS6\ShopBundle\Model\Category\CategoryFacade
	 */
	private $categoryFacade;

	/**
	 * @var \Symfony\Component\Translation\Translator
	 */
	private $translator;

	/**
	 * @var \SS6\ShopBundle\Model\Product\MassAction\ProductMassActionFacade
	 */
	private $productMassActionFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Grid\GridFactory
	 */
	private $gridFactory;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductEditFacade
	 */
	private $productEditFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Detail\ProductDetailFactory
	 */
	private $productDetailFactory;

	/**
	 * @var \SS6\ShopBundle\Form\Admin\Product\ProductEditFormTypeFactory
	 */
	private $productEditFormTypeFactory;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductEditDataFactory
	 */
	private $productEditDataFactory;

	/**
	 * @var \SS6\ShopBundle\Model\AdminNavigation\Breadcrumb
	 */
	private $breadcrumb;

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\Group\PricingGroupFacade
	 */
	private $pricingGroupFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Administrator\AdministratorGridFacade
	 */
	private $administratorGridFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\Listing\ProductListAdminFacade
	 */
	private $productListAdminFacade;

	/**
	 * @var \SS6\ShopBundle\Model\AdvancedSearch\AdvancedSearchFacade
	 */
	private $advancedSearchFacade;

	/**
	 * @var \SS6\ShopBundle\Model\Product\ProductVariantFacade
	 */
	private $productVariantFacade;

	/**
	 * @var \SS6\ShopBundle\Twig\ProductExtension
	 */
	private $productExtension;

	public function __construct(
		CategoryFacade $categoryFacade,
		Translator $translator,
		ProductMassActionFacade $productMassActionFacade,
		GridFactory $gridFactory,
		EntityManager $em,
		ProductEditFacade $productEditFacade,
		ProductDetailFactory $productDetailFactory,
		ProductEditFormTypeFactory $productEditFormTypeFactory,
		ProductEditDataFactory $productEditDataFactory,
		Breadcrumb $breadcrumb,
		PricingGroupFacade $pricingGroupFacade,
		AdministratorGridFacade $administratorGridFacade,
		ProductListAdminFacade $productListAdminFacade,
		AdvancedSearchFacade $advancedSearchFacade,
		ProductVariantFacade $productVariantFacade,
		ProductExtension $productExtension
	) {
		$this->categoryFacade = $categoryFacade;
		$this->translator = $translator;
		$this->productMassActionFacade = $productMassActionFacade;
		$this->gridFactory = $gridFactory;
		$this->em = $em;
		$this->productEditFacade = $productEditFacade;
		$this->productDetailFactory = $productDetailFactory;
		$this->productEditFormTypeFactory = $productEditFormTypeFactory;
		$this->productEditDataFactory = $productEditDataFactory;
		$this->breadcrumb = $breadcrumb;
		$this->pricingGroupFacade = $pricingGroupFacade;
		$this->administratorGridFacade = $administratorGridFacade;
		$this->productListAdminFacade = $productListAdminFacade;
		$this->advancedSearchFacade = $advancedSearchFacade;
		$this->productVariantFacade = $productVariantFacade;
		$this->productExtension = $productExtension;
	}

	/**
	 * @Route("/product/edit/{id}", requirements={"id" = "\d+"})
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function editAction(Request $request, $id) {
		$product = $this->productEditFacade->getById($id);

		$form = $this->createForm($this->productEditFormTypeFactory->create($product));
		$productEditData = $this->productEditDataFactory->createFromProduct($product);

		$form->setData($productEditData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$this->em->transactional(
				function () use ($id, $form) {
					$this->productEditFacade->edit($id, $form->getData());
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig('Bylo upraveno zboží <strong>{{ product|productDisplayName }}</strong>', [
				'product' => $product,
			]);
			return $this->redirect($this->generateUrl('admin_product_edit', ['id' => $product->getId()]));
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlashTwig('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		$this->breadcrumb->replaceLastItem(
			new MenuItem($this->translator->trans('Editace zboží - ') . $this->productExtension->getProductDisplayName($product))
		);

		return $this->render('@SS6Shop/Admin/Content/Product/edit.html.twig', [
			'form' => $form->createView(),
			'product' => $product,
			'productDetail' => $this->productDetailFactory->getDetailForProduct($product),
			'productSellingPricesIndexedByDomainId' => $this->productEditFacade->getAllProductSellingPricesIndexedByDomainId($product),
			'productMainCategoriesIndexedByDomainId' => $this->categoryFacade->getProductMainCategoriesIndexedByDomainId($product),
		]);
	}

	/**
	 * @Route("/product/new/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function newAction(Request $request) {
		$form = $this->createForm($this->productEditFormTypeFactory->create());

		$productEditData = $this->productEditDataFactory->createDefault();

		$form->setData($productEditData);
		$form->handleRequest($request);

		if ($form->isValid()) {
			$product = $this->em->transactional(
				function () use ($form) {
					return $this->productEditFacade->create($form->getData());
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig('Bylo vytvořeno zboží'
					. ' <strong><a href="{{ url }}">{{ product|productDisplayName }}</a></strong>', [
				'product' => $product,
				'url' => $this->generateUrl('admin_product_edit', ['id' => $product->getId()]),
			]);
			return $this->redirect($this->generateUrl('admin_product_list'));
		}

		if ($form->isSubmitted() && !$form->isValid()) {
			$this->getFlashMessageSender()->addErrorFlashTwig('Prosím zkontrolujte si správnost vyplnění všech údajů');
		}

		return $this->render('@SS6Shop/Admin/Content/Product/new.html.twig', [
			'form' => $form->createView(),
			'pricingGroupsIndexedByDomainId' => $this->pricingGroupFacade->getAllIndexedByDomainId(),
		]);
	}

	/**
	 * @Route("/product/list/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function listAction(Request $request) {
		$administrator = $this->getUser();
		/* @var $administrator \SS6\ShopBundle\Model\Administrator\Administrator */

		$advancedSearchForm = $this->advancedSearchFacade->createAdvancedSearchForm($request);
		$advancedSearchData = $advancedSearchForm->getData();

		$quickSearchForm = $this->createForm(new QuickSearchFormType());
		$quickSearchData = new QuickSearchFormData();
		$quickSearchForm->setData($quickSearchData);

		// Cannot call $form->handleRequest() because the GET forms are not handled in POST request.
		// See: https://github.com/symfony/symfony/issues/12244
		$quickSearchForm->submit($request->query->get($quickSearchForm->getName()));

		$massActionForm = $this->createForm(new ProductMassActionFormType($this->translator));
		$massActionForm->handleRequest($request);

		$isAdvancedSearchFormSubmitted = $this->advancedSearchFacade->isAdvancedSearchFormSubmitted($request);
		if ($isAdvancedSearchFormSubmitted) {
			$queryBuilder = $this->advancedSearchFacade->getQueryBuilderByAdvancedSearchData($advancedSearchData);
		} else {
			$queryBuilder = $this->productListAdminFacade->getQueryBuilderByQuickSearchData($quickSearchData);
		}

		$grid = $this->getGrid($queryBuilder);

		if ($massActionForm->get('submit')->isClicked()) {
			$this->productMassActionFacade->doMassAction(
				$massActionForm->getData(),
				$queryBuilder,
				array_map('intval', $grid->getSelectedRowIds())
			);

			$this->getFlashMessageSender()->addSuccessFlash('Hromadná úprava byla provedena');

			return $this->redirect($this->getRequest()->headers->get('referer', $this->generateUrl('admin_product_list')));
		}

		$this->administratorGridFacade->restoreAndRememberGridLimit($administrator, $grid);

		return $this->render('@SS6Shop/Admin/Content/Product/list.html.twig', [
			'gridView' => $grid->createView(),
			'quickSearchForm' => $quickSearchForm->createView(),
			'advancedSearchForm' => $advancedSearchForm->createView(),
			'massActionForm' => $massActionForm->createView(),
			'isAdvancedSearchFormSubmitted' => $this->advancedSearchFacade->isAdvancedSearchFormSubmitted($request),
		]);
	}

	/**
	 * @Route("/product/delete/{id}", requirements={"id" = "\d+"})
	 * @CsrfProtection
	 * @param int $id
	 */
	public function deleteAction($id) {
		try {
			$product = $this->productEditFacade->getById($id);

			$this->em->transactional(
				function () use ($id) {
					$this->productEditFacade->delete($id);
				}
			);

			$this->getFlashMessageSender()->addSuccessFlashTwig('Produkt <strong>{{ product|productDisplayName }}</strong> byl smazán', [
				'product' => $product,
			]);
		} catch (\SS6\ShopBundle\Model\Product\Exception\ProductNotFoundException $ex) {
			$this->getFlashMessageSender()->addErrorFlash('Zvolený produkt neexistuje.');
		}

		return $this->redirect($this->generateUrl('admin_product_list'));
	}

	/**
	 * @Route("/product/get-advanced-search-rule-form/", methods={"post"})
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function getRuleFormAction(Request $request) {
		$ruleForm = $this->advancedSearchFacade->createRuleForm($request->get('filterName'), $request->get('newIndex'));

		return $this->render('@SS6Shop/Admin/Content/Product/AdvancedSearch/ruleForm.html.twig', [
			'rulesForm' => $ruleForm->createView(),
		]);
	}

	/**
	 * @Route("/product/create-variant/")
	 * @param \Symfony\Component\HttpFoundation\Request $request
	 */
	public function createVariantAction(Request $request) {
		$form = $this->createForm(new VariantFormType());

		$form->handleRequest($request);
		if ($form->isValid()) {
			$formData = $form->getData();
			$mainVariant = $formData[VariantFormType::MAIN_VARIANT];
			try {
				$newMainVariant = $this->em->transactional(
					function () use ($mainVariant, $formData) {
						return $this->productVariantFacade->createVariant(
							$mainVariant,
							$formData[VariantFormType::VARIANTS]
						);
					}
				);

				$this->getFlashMessageSender()->addSuccessFlashTwig(
					'Varianta <strong>{{ productVariant|productDisplayName }}</strong> byla úspěšně vytvořena.', [
						'productVariant' => $newMainVariant,
					]
				);

				return $this->redirectToRoute('admin_product_edit', ['id' => $newMainVariant->getId()]);
			} catch (\SS6\ShopBundle\Model\Product\Exception\VariantException $ex) {
				$this->getFlashMessageSender()->addErrorFlash(
					'Nelze vytvářet varianty ze zboží, které jsou již variantou nebo hlavní variantou.'
				);
			}
		}

		return $this->render('@SS6Shop/Admin/Content/Product/createVariant.html.twig', [
			'form' => $form->createView(),
		]);
	}

	/**
	 * @param \Doctrine\ORM\QueryBuilder $queryBuilder
	 * @return \SS6\ShopBundle\Model\Grid\Grid
	 */
	private function getGrid(QueryBuilder $queryBuilder) {
		$dataSource = new QueryBuilderWithRowManipulatorDataSource(
			$queryBuilder,
			'p.id',
			function ($row) {
				$product = $this->productEditFacade->getById($row['p']['id']);
				$row['product'] = $product;
				return $row;
			}
		);

		$grid = $this->gridFactory->create('productList', $dataSource);
		$grid->enablePaging();
		$grid->enableSelecting();
		$grid->setDefaultOrder('name');

		$grid->addColumn('name', 'pt.name', 'Název', true);
		$grid->addColumn('price', 'p.price', 'Cena', true)->setClassAttribute('text-right');
		$grid->addColumn('visible', 'p.visible', 'Viditelnost', true)->setClassAttribute('text-center table-col table-col-10');

		$grid->setActionColumnClassAttribute('table-col table-col-10');
		$grid->addActionColumn('edit', 'Upravit', 'admin_product_edit', ['id' => 'p.id']);
		$grid->addActionColumn('delete', 'Smazat', 'admin_product_delete', ['id' => 'p.id'])
			->setConfirmMessage('Opravdu chcete odstranit toto zboží?');

		$grid->setTheme('@SS6Shop/Admin/Content/Product/listGrid.html.twig');

		return $grid;
	}

}
