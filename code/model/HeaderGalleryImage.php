<?php



/**
 * StartGeneratedWithDataObjectAnnotator
 * @property string Title
 * @property string Copyright
 * @property string Caption
 * @property int SortOrder
 * @property int AttachmentID
 * @property int ResourcePageID
 * @property int SiteConfigID
 * @method Image Attachment
 * @method Page ResourcePage
 * @method SiteConfig SiteConfig
 * EndGeneratedWithDataObjectAnnotator
 */
class HeaderGalleryImage extends DataObject {


	private static $db = array(
		'Title' => 'Text',
		'Copyright' => 'Text',
		'Caption' => 'Text',
		'SortOrder' => 'Int'
	);

	private static $has_one = array(
		'Attachment' => 'Image',
		'ResourcePage' => 'Page',
		'SiteConfig' => 'SiteConfig'
	);


	private static $default_sort = 'SortOrder ASC';

	private static $delete_permission = "CMS_ACCESS_CMSMain";

	public function getCMSFields() {
		$fields = FieldList::create(
			TextField::create('Title'),
			TextField::create('Copyright'),
            TextareaField::create('Caption'),
			$imageField = UploadField::create('Attachment')
		);
		$imageField->setAllowedFileCategories('image');
		$imageField->setAllowedMaxFileNumber(1);

		return $fields;

	}

	/**
	 * Show delete Button in ImageGalleryManager
	 */

	public function canDelete($member = null) {
		return Permission::check($this->stat('delete_permission'));
	}

	public function canView($member = null) {
		return true;
	}

	public function canCreate($member = null) {
		return Permission::check($this->stat('delete_permission'));
	}

	public function canEdit($member = null) {
		return Permission::check($this->stat('delete_permission'));
	}

	/**
	 * shortcut for displaying copyright information and caption
	 * @return string
	 */
	public function getDescription() {
		return join(' - ', array_filter([$this->Caption, $this->getCopy()]));
	}

	/**
	 * replaces "(c)" with html copyright sign
	 * @return mixed
	 */
	public function getCopy() {
		return str_replace('(c)', '&copy;', $this->Copyright);
	}
}
