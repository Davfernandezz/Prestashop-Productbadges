{**
 * product-badges.tpl
 * Renderiza las etiquetas visuales sobre la imagen del producto
 *}

{if isset($badges) && $badges|@count > 0}
<div class="productbadges-wrapper">
    {foreach from=$badges item=badge}
        {* Sanitización: escapamos todos los valores antes de volcarlos en HTML *}
        {assign var=safeLabel     value=$badge.label|escape:'html':'UTF-8'}
        {assign var=safeBgColor   value=$badge.bg_color|escape:'html':'UTF-8'}
        {assign var=safeTextColor value=$badge.text_color|escape:'html':'UTF-8'}

        {* Validamos la posición contra valores permitidos para evitar CSS injection *}
        {if $badge.position == 'top-right'}
            {assign var=positionClass value='productbadge--top-right'}
        {else}
            {assign var=positionClass value='productbadge--top-left'}
        {/if}

        <span
            class="productbadge {$positionClass|escape:'html':'UTF-8'}"
            style="background-color:{$safeBgColor};color:{$safeTextColor};"
            aria-label="{$safeLabel}"
        >
            {$safeLabel}
        </span>
    {/foreach}
</div>
{/if}
