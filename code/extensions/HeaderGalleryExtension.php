<?php

/**
 * StartGeneratedWithDataObjectAnnotator
 * @method DataList|HeaderGalleryImage[] HeaderGallery
 * EndGeneratedWithDataObjectAnnotator
 */
class HeaderGalleryExtension extends DataExtension {

	private static $has_many = array(
		'HeaderGallery' => 'HeaderGalleryImage'
	);

	/**
	 * Limit the number of displayed header images
	 * If set to 1 we have a single upload field
	 * @var int
	 */
	private static $limit_header_images = 0;

	private static $delete_permission = "CMS_ACCESS_CMSMain";


	function updateCMSFields(FieldList $fields) {
		/**
		 * @var GridFieldConfig $conf
		 */
		$conf = GridFieldConfig_RecordEditor::create(10);
		$conf->addComponent(new GridFieldSortableRows('SortOrder'));
		$conf->addComponent(new GridFieldGalleryTheme('Attachment'));
		$conf->addComponent(new GridFieldBulkUpload());
		$conf->getComponentByType('GridFieldBulkUpload')->setUfSetup('setFolderName', 'header');

		if(class_exists('GridFieldPaginatorWithShowAll')) {
			$conf->removeComponentsByType('GridFieldPaginator');
			$conf->addComponent(new GridFieldPaginatorWithShowAll(10));
		}

		$fields->addFieldToTab(
			"Root." . _t('HeaderGalleryExtension.GalleryTabName', 'Header Gallery'),
			Gridfield::create(
				'HeaderGallery',
				_t('HeaderGalleryExtension.GalleryFieldTitle', 'Gallery in header'),
				$this->owner->HeaderGallery(),
				$conf
			)
		);

	}

	/**
	 * Helper for getting the header gallery in the template
	 * Tries to find the current gallery, falls back to parent pages and home-page or SiteConfig if it has a standard gallery
	 * @return null|void
	 */
	public function getHeaderPics() {
		$owner = $this->getGalleryOwner();

		if(!$owner->ID) return;

		if(isset($owner->ID) && $owner->HeaderGallery()->count() > 0) {
			return $owner->HeaderGallery();
		}

		if($owner->ParentID && $gallery = $this->getGalleryOfParent($owner)) {
			return $gallery;
		}

		return $this->getDefaultHeaderPics();
	}

	public function getGalleryOwner() {
		$owner = $this->owner;

		//overwrite owner in Translatable or Subsite setups
		$this->owner->extend('updateGalleryOwner', $owner);

		return $owner;
	}

	public function getGalleryOfParent($owner) {
		$Parent = $owner->Parent();

		return (is_object($Parent) && $Parent->ID != 0)
			? $Parent->getHeaderPics()
			: null;
	}

	/**
	 * Helper to get the default header pics from e.g. SiteConfig
	 * @return DataList|null|SS_Limitable
	 */
	public function getDefaultHeaderPics() {
		$default = null;

		$this->owner->extend('updateDefaultHeaderPics', $default);

		if(!$default && class_exists('SiteConfig')) {
			$siteconfig = SiteConfig::current_site_config();
			if ($siteconfig->hasMethod('HeaderGallery')) {
				$default = $this->LimitGalleryItems($siteconfig->HeaderGallery());
			}
		}

		return $default;
	}

	/**
	 * limits the number of displayed items
	 *
	 * @param DataList $galleryList
	 * @return DataList|SS_Limitable
	 */
	protected function LimitGalleryItems(DataList $galleryList) {
		$limit = Config::inst()->get('HeaderGalleryExtension', 'limit_header_images');

		return $limit ? $galleryList->limit($limit) : $galleryList;
	}
}

