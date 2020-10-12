<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Category\Save;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Cms\Api\GetBlockByIdentifierInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Test cases for save category controller.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation disabled
 */
class SaveCategoryTest extends AbstractSaveCategoryTest
{
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var GetBlockByIdentifierInterface */
    private $getBlockByIdentifier;

    /** @var string */
    private $createdCategoryId;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->categoryRepository = $this->_objectManager->get(CategoryRepositoryInterface::class);
        $this->getBlockByIdentifier = $this->_objectManager->get(GetBlockByIdentifierInterface::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        try {
            $this->categoryRepository->deleteByIdentifier($this->createdCategoryId);
        } catch (NoSuchEntityException $e) {
            //Category already deleted.
        }
        $this->createdCategoryId = null;

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     *
     * @return void
     */
    public function testCreateCategoryWithCmsBlock(): void
    {
        $blockId = $this->getBlockByIdentifier->execute('fixture_block', 1)->getId();
        $postData = [
            CategoryInterface::KEY_NAME => 'Category with cms block',
            CategoryInterface::KEY_IS_ACTIVE => 1,
            CategoryInterface::KEY_INCLUDE_IN_MENU => 1,
            'display_mode' => Category::DM_MIXED,
            'landing_page' => $blockId,
            'available_sort_by' => 1,
            'default_sort_by' => 1,
        ];
        $responseData = $this->performSaveCategoryRequest($postData);
        $this->assertRequestIsSuccessfullyPerformed($responseData);
        $this->createdCategoryId = $responseData['category']['entity_id'];
        $category = $this->categoryRepository->get($this->createdCategoryId);
        $this->assertEquals($blockId, $category->getLandingPage());
    }
}
