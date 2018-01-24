[{$smarty.block.parent}]
[{assign var="oConfig" value=$oViewConf->getConfig()}]
[{assign var="nl2GoCompanyId" value=$oViewConf->getCompanyId()}]

[{if $nl2GoCompanyId neq ''}]
    <script id="n2g_script">
        !function(e,t,n,c,r,a,i){e.Newsletter2GoTrackingObject=r,e[r]=e[r]||
        function(){(e[r].q=e[r].q||[]).push(arguments)},e[r].l=1*new Date,a=t.createElement(n),
                i=t.getElementsByTagName(n)[0],a.async=1,a.src=c,i.parentNode.insertBefore(a,i)}
        (window,document,"script","//static.newsletter2go.com/utils.js","n2g");
        n2g('create', '[{$nl2GoCompanyId}]');
        n2g('ecommerce:addTransaction', {
            'id': '[{$order->oxorder__oxid->value}]',
            'affiliation': '[{$oxcmp_shop->oxshops__oxname->value}]',
            'revenue': '[{$order->oxorder__oxtotalordersum->value|string_format:"%.2f"}]',
            'shipping': '[{$order->oxorder__oxdelcost->value}]',
            'tax': '[{$order->oxorder__oxartvatprice1->value|string_format:"%.2f"}]'
        });
        [{foreach from=$order->getOrderArticles(true) item=orderitem name=testOrderItem}]
            [{assign var="sCategoryIds" value=$orderitem->getCategoryIds()}]
            [{assign var="sCategoryName" value=$oViewConf->getCategoryName($sCategoryIds)}]
            n2g('ecommerce:addItem', {
                'id': '[{$order->oxorder__oxid->value}]',
                'name': '[{$orderitem->oxorderarticles__oxtitle->value}]',
                'sku': '[{$orderitem->oxorderarticles__oxartnum->value}]',
                'category': '[{$sCategoryName}]',
                'price': '[{$orderitem->oxorderarticles__oxprice->value|string_format:"%.2f"}]',
                'quantity': '[{$orderitem->oxorderarticles__oxamount->value}]'
            });
        [{/foreach}]
        n2g('ecommerce:send');
    </script>
[{/if}]