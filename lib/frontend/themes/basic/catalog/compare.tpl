<div class="compare_clear_list {if {strlen($error_text) > 0}} error_compare{/if}"><a href="javascript:clear_compare()" class="clear_all">{$smarty.const.TEXT_CLEAR_COMPARE_LIST}</a></div>
<div class="compare_list">
{if {strlen($error_text) > 0}}
    {$error_text}
{else}

{if {$products_data_array|@count} > 0}
  <table cellspacing="0" cellpadding="0" width="100%">
    <tr>
      <th width="{intval(100/($products_data_array|@count + 1))}%"></th>
    {foreach $products_data_array as $products_id => $product}
      <th width="{intval(100/($products_data_array|@count + 1))}%"><a class="btn-del" href="javascript:clear_compare_product({$products_id})">&nbsp;</a></th>
    {/foreach}
    </tr>
    <tr>
      <td>{$smarty.const.TEXT_IMAGE}</td>
    {foreach $products_data_array as $products_id => $product}
      <td><a href="{$product.link}"><img src="{$product.image}" alt="{$product.name}"></a></td>
    {/foreach}
    </tr>
    <tr>
      <td>{$smarty.const.TEXT_NAME}</td>
    {foreach $products_data_array as $products_id => $product}
      <td><a href="{$product.link}">{$product.name}</a></td>
    {/foreach}
    </tr>
    <tr>
      <td>{$smarty.const.TEXT_MODEL}</td>
    {foreach $products_data_array as $products_id => $product}
      <td>{$product.model}</td>
    {/foreach}
    </tr>
    {if $smarty.const.GROUPS_IS_SHOW_PRICE}
    <tr>
      <td>{$smarty.const.TEXT_PRICE}</td>
    {foreach $products_data_array as $products_id => $product}
      <td class="price">
      {if isset($product.price)}
        <span class="current">{$product.price}</span>
      {else}
        <span class="old">{$product.price_old}</span>
        <span class="specials">{$product.price_special}</span>
      {/if}
      </td>
    {/foreach}
    </tr>
    {/if}
  {if !$smarty.const.GROUPS_DISABLE_CHECKOUT}
    <tr>
      <td>&nbsp;</td>
    {foreach $products_data_array as $products_id => $product}
      <td><a href="{$product.link_buy}" class="btn-1 btn-buy" rel="nofollow">{$smarty.const.BUY_NOW}</a></td>
    {/foreach}
    </tr>
  {/if}
  {if {$properties_tree_array|@count} > 0}
    <tr class="vary">
      <td style="text-align:right">{$smarty.const.TEXT_KEY}</td>
      <td colspan="{$products_data_array|@count}">{$smarty.const.TEXT_COMPARE_PRODUCT_FEATURES}</td>
    </tr>
    {foreach $properties_tree_array as $properties_id => $property}
    <tr id="property-{$property['properties_id']}" {if {$property['vary']}} class="vary"{/if}>
      <td class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}" valign="top">{$property['throughoutID']} {$property['properties_name']}</td>
      {foreach $products_data_array as $products_id => $products_data}
      <td class="level-{count(explode('.', $property['throughoutID']))} {$property['properties_type']}" valign="top">
          {if {$products_data['properties_tree'][$properties_id]['values']|@count} > 0}
            {foreach $products_data['properties_tree'][$properties_id]['values'] as $value_id => $value}
            <span id="value-{$value_id}">{$value}</span>
            {/foreach}
          {/if}
      </td>
      {/foreach}
    </tr>
    {/foreach}
  {/if}
  </table>
{/if}

{/if}
</div>
<script type="text/javascript">
  tl(function(){
    $('.compare_list').css('height',$(window).height()*0.75)
  });

function clear_compare() {
    $('input[name="compare[]"]').each(function(i, e) {
        e.checked = false;
    })
    $('.pop-up-close').trigger('click');
}

function clear_compare_product(prid) {
    $('input[name="compare[]"]').each(function(i, e) {
        if (e.value == prid) {
            e.checked = false;
        }
    })
    $('.pop-up-close').trigger('click');
    $('.compare_button').trigger('click');
}
</script>
    