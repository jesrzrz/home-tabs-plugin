<?php

class HomeCategoryTab extends Module
{
	private $_html = '';
	private $_postErrors = array();

	function __construct()
	{
		$this->name = 'homecategorytab';
		$this->tab = 'front_office_features';
		$this->version = '1.2';

		parent::__construct(); 

		$this->page = basename(__FILE__, '.php');
		$this->displayName = $this->l('Add Multiple Categories to Home  Tab');
		$this->description = $this->l('Display products in the homepage tab by category');
	}

	function install()
	{
		if (!Configuration::updateValue('HOME_TAB_CAT_NBR', 4) || !parent::install() || !$this->registerHook('displayHomeTab')
			|| !$this->registerHook('displayHomeTabContent') )
			return false;
		if (!Configuration::updateValue('HOME_TAB_CATEGORIES', 3))
			return false;
		return true;
	}
	public function uninstall()
	{
		if (!parent::uninstall() ||
			!$this->_deleteContent())
			return false;
		return true;
	}
	
		
	public function hookDisplayHeader()
	{
		
	}

	public function getContent()
	{
		$output = '<h2>'.$this->displayName.'</h2>';
		if (Tools::isSubmit('submitHomehbc'))
		{
			$nbr = intval(Tools::getValue('nbr'));
			$sort = intval(Tools::getValue('sort'));
			$tabbercat = Tools::getValue('tabbercat');

			if (!$nbr OR $nbr <= 0 OR !Validate::isInt($nbr))
				$errors[] = $this->l('Invalid number of product');
			else
				Configuration::updateValue('HOME_TAB_CAT_NBR', $nbr);

			if (!empty($tabbercat))
				Configuration::updateValue('HOME_TAB_CATEGORIES', implode(',',$tabbercat));

			if (isset($errors) AND sizeof($errors))
				$output .= $this->displayError(implode('<br />', $errors));
			else
				$output .= $this->displayConfirmation($this->l('Settings updated'));
		}
		return $output.$this->displayForm();
	}
	
	function recurseCategory($categories, $current, $id_category = 1, $selectids_array)
	{
		global $currentIndex;		

		echo '<option value="'.$id_category.'"'.(in_array($id_category,$selectids_array) ? ' selected="selected"' : '').'>'.
		str_repeat('&nbsp;', $current['infos']['level_depth'] * 5) . preg_replace('/^[0-9]+\./', '', stripslashes($current['infos']['name'])) . '</option>';
		if (isset($categories[$id_category]))
			foreach ($categories[$id_category] AS $key => $row)
				$this->recurseCategory($categories, $categories[$id_category][$key], $key, $selectids_array);
	}
	

	public function displayForm()
	{
		global $cookie,$currentIndex;
	
		$output = '
					<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
						<fieldset><legend><img src="'.$this->_path.'logo.gif" alt="" title="" />'.$this->l('Settings').'</legend>
						
												
						<label>'.$this->l('Number of product displayed').'</label>
						<div class="margin-form">
							<input type="text" size="5" name="nbr" value="'.Tools::getValue('nbr', Configuration::get('HOME_TAB_CAT_NBR')).'" />
							<p class="clear">'.$this->l('The number of products displayed on homepage category tab (default: 4)').'</p>
						</div>
					';
					
		/* Retrieval of the shop cats to construct the multiple select */
		
		/* Get Tab Categoty */
		$tabbercat = Configuration::get('HOME_TAB_CATEGORIES');
		
		if (!empty($tabbercat))
		{
			$tabbercat_array = explode(',',$tabbercat);
		}
		else
		{
			$tabbercat_array = array();
		}
		
		/* cat select */
		$output .= '
						<label>'.$this->l('Shop categories to include').'</label>
						<div class="margin-form">		
							<select name="tabbercat[]" multiple="multiple" width="300" style="width: 300px">';
		$categories = Category::getCategories(intval($cookie->id_lang));
		ob_start();
		$this->recurseCategory($categories, $categories[1][1], 1, $tabbercat_array);
		$output .= ob_get_contents();
		ob_end_clean();
		$output .= '
							</select>
							<p class="clear">'.$this->l('Select the categories you want to include  in the homepage tab (Hold CTRL to select multiples)').'</p>									
						</div>						
					';
				
		$output .= '
									
						<center><input type="submit" name="submitHomehbc" value="'.$this->l('Save').'" class="button" /></center>
		
					</fieldset>
				</form>
			  ';
		return $output;
	}


private function getCategoryIDs()
	{
	$catids = Configuration::get('HOME_TAB_CATEGORIES');
	if (strlen($catids))
	return explode(',', Configuration::get('HOME_TAB_CATEGORIES'));
	else
	return array();
}
	
public function hookDisplayHomeTab($params)
{
	global $smarty;
	
$cat_ids = $this->getCategoryIDs();
$id_lang = (int)$this->context->language->id;
$id_shop = (int)Shop::getContextShopID();
$categories = array();	
	
foreach ($cat_ids as $cat_id) {
	if (!$cat_id)
	continue;
$category = new Category((int)$cat_id, (int)$id_lang);
if (Validate::isLoadedObject($category)) {
$hometabcats[$cat_id]['id'] = $cat_id;
$hometabcats[$cat_id]['name'] = $category -> name;
}
}
$smarty->assign(array('hometab_categories' => $hometabcats));
return $this->display(__FILE__, 'cattab.tpl');
}
	
	
	
	

function hookDisplayHomeTabContent($params)
{
global $smarty;
$np = Configuration::get('HOME_TAB_CAT_NBR');
$cat_ids = $this->getCategoryIDs();
$id_lang = (int)$this->context->language->id;
$id_shop = (int)Shop::getContextShopID();
$categories = array();	
	
foreach ($cat_ids as $cat_id) {
	if (!$cat_id)
	continue;
$category = new Category((int)$cat_id, (int)$id_lang);
if (Validate::isLoadedObject($category)) {
$hometabcatsprods[$cat_id]['id'] = $cat_id;
$hometabcatsprods[$cat_id]['name'] = $category -> name;

$hometabcatsprods[$cat_id]['products'] = $category -> getProducts($id_lang, 1, ($np ? $np : 4));
}
}
		
$smarty->assign(array('categories' => $hometabcatsprods));
return $this->display(__FILE__, 'homecategorytab.tpl');
}

	private function _deleteContent()
	{
		if (!Configuration::deleteByName('HOME_TAB_CAT_NBR') ||
			!Configuration::deleteByName('HOME_TAB_CATEGORIES'))
			return false;
		return true;

	}

}
