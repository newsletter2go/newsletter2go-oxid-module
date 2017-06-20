<?php

class nl2goFrontModel extends nl2goFrontModel_parent
{

    public function render()
    {
        return $this;
    }

    /**
     * Returns company id if it's set and tracking is enabled
     *
     * @return mixed|string
     */
    public function getCompanyId()
    {
        $result = '';

        $trackingEnabled = oxRegistry::getConfig()->getConfigParam('nl2goTracking');
        $companyId = oxRegistry::getConfig()->getConfigParam('nl2goCompanyId');

        if (!empty($companyId) && !empty($trackingEnabled)) {
            $result = $companyId;
        }

        return $result;
    }

    /**
     * Returns last category title based on ids
     *
     * @param $categoryIds
     * @return string
     */
    public function getCategoryName($categoryIds)
    {
        $categoryName = '';

        foreach ($categoryIds as $categoryId) {
            /** @var oxCategory $oxCategory */
            $oxCategory = oxNew('oxcategory');
            if ($oxCategory->load($categoryId)) {
                $categoryName = $oxCategory->getTitle();
            }
        }

        return $categoryName;
    }
}
