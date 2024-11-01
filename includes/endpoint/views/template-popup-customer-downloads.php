<!-- Customer downloads JWT -->
<script id="ntvwc-template-popup-customer-purchased-token" type="text/template">
	<div id="ntvwc-popup-customer-purchased-token-outer-wrapper-<%- tokenId %>" class="ntvwc-popup-customer-purchased-token">
		<div id="customer-download-jwt-inner-wrapper-<%- tokenId %>" class="ntvwc-popup-customer-purchased-token-inner-wrapper">
			<textarea id="ntvwc-textarea-customer-purchased-token-<%- tokenId %>" class="ntvwc-textarea-customer-purchased-token" disabled><%- token %></textarea>
			<div class="ntvwc-copy-close-buttons">
				<a id="ntvwc-copy-text-<%- tokenId %>" class="ntvwc-button ntvwc-copy-text" href="javascript: void(0);" data-token-id="<%- tokenId %>"><%- textCopy %></a>
				<a id="ntvwc-close-popup-<%- tokenId %>" class="ntvwc-button ntvwc-close-popup" href="javascript: void(0);" data-token-id="<%- tokenId %>"><%- textClose %></a>
			</div>
		</div>
	</div>
</script>