<?php
declare(strict_types=1);

namespace Pixel\Subcategories\ViewModel;

use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Framework\Registry;
use \Magento\Catalog\Model\CategoryFactory;
use \Magento\Catalog\Helper\Output;
use \Magento\Framework\View\Element\Template\Context;


class Subcategories extends \Magento\Framework\View\Element\Template implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /** @var StoreManagerInterface */
    private $storeManager;

    /** @var Repository */
    private $viewAssetRepo;

    /** @var Registry */
    private $coreRegistry;

    /** @var CategoryFactory */
    private $categoryFactory;

    /** @var Output */
    private $catalogHelperOutput;

    /** @var Context */
    protected $context;

    public function __construct(
        StoreManagerInterface $storeManager,
        Repository $viewAssetRepo,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        Output $catalogHelperOutput,
        Context $context, array $data = []
    )
    {
        $this->storeManager = $storeManager;
        $this->viewAssetRepo = $viewAssetRepo;
        $this->coreRegistry = $coreRegistry;
        $this->categoryFactory = $categoryFactory;
        $this->catalogHelperOutput = $catalogHelperOutput;
        parent::__construct($context, $data);
    }

    public function getCategories()
    {

        $category = $this->getCurrentCategory();
        if (!$category) return;

        $categoryId = $category->getId();

        $sortAttribute = $this->getSortAttribute();
        $model = $this->categoryFactory->create();
        $categories = $model->getCollection()
            ->addAttributeToSelect(['name', 'url_key', 'url_path', 'image'])
            ->addAttributeToFilter('parent_id', $categoryId)
            ->addAttributeToSort($sortAttribute)
            ->addIsActiveFilter();

        return $categories;
    }

    public function getImage($category)
    {
        $placeholderImageUrl = $this->viewAssetRepo->getUrl(
            'Magento_Catalog::images/product/placeholder/small_image.jpg'
        );
        $image = $category->getImage();
        if ($image != null) {
            $url = $this->getImageUrl($image);
        } else {
            $url = $placeholderImageUrl;
        }
        return $url;
    }

    public function getImageUrl($image)
    {
        $url = false;
        if ($image) {
            if (substr($image, 0, 1) === '/') {
                $url = $this->storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_WEB
                    ) . ltrim($image, '/');
            } else {
                $url = $this->storeManager->getStore()->getBaseUrl(
                        \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                    ) . 'catalog/category/' . $image;
            }
        }
        return $url;
    }

    public function getCurrentCategory()
    {
        return $this->coreRegistry->registry('current_category');
    }

    public function getCategoryID()
    {

        return $this->getCurrentCategory()->getData('entity_id');

    }

    public function getCategoryLevelCSS()
    {

        $categoryLevelCSS = "";
        $categoryLevel = $this->getCurrentCategory()->getData('level');

        if ($categoryLevel == 2) {
            $categoryLevelCSS = "landing-category";
        } elseif ($categoryLevel == 3) {
            $categoryLevelCSS = "subcategory-category";
        }
        return $categoryLevelCSS;
    }
}
