	{if isset($categories) AND $categories}
			{foreach from=$categories item=category}
			{if isset($category.products) AND $category.products}			
			<div id="cattab{$category.id}"  class="tab-pane fade">
				{include file="$tpl_dir./product-list.tpl" products=$category.products productimg=$category.productimg}	
			</div>
			{/if}
			{/foreach}

	{/if}