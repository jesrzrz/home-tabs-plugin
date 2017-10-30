	{foreach from=$hometab_categories item=homecategory}	
		{$catid=$homecategory.id_category}		
        <li><a data-toggle="tab" href="#cattab{$homecategory.id}">{$homecategory.name|escape:'htmlall':'UTF-8'|truncate:50:'...'}</a></li> 
	{/foreach}

	
