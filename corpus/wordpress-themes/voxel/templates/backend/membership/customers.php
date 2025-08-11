<div class="wrap">
	<h1><?= get_admin_page_title() ?></h1>
	<form method="get">
		<input type="hidden" name="page" value="<?= esc_attr( $_REQUEST['page'] ) ?>" />
		<input type="hidden" name="plan" value="<?= esc_attr( $_REQUEST['plan'] ?? '' ) ?>" />
		<?php $table->views() ?>
		<?php $table->search_box( 'Search customers', 'search' ) ?>
		<?php $table->display() ?>
	</form>
</div>

<style type="text/css">
	.column-title img {
		margin-right: 10px;
		border-radius: 50px;
		display: inline-block;
		vertical-align: middle;
	}

	#search-search-input {
		width: 250px;
	}

	#id, .column-id  { width: 50px; }
	#id a, .column-id {
		padding-right: 0 !important;
		padding-left: 0 !important;
	}

	.column-details, #details { width: 66px; }

	.column-amount .price-amount {
		font-size: 16px;
	}

	#the-list td {
		vertical-align: middle;
	}

	.item-title {
		font-weight: 600;
		vertical-align: middle;
	}
</style>

<script type="text/javascript">
	jQuery( $ => {
		$('#search-search-input').attr('placeholder', 'Enter name, email, or user id');
	} );
</script>
