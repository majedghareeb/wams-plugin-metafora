import { registerBlockType } from '@wordpress/blocks';
import ServerSideRender from '@wordpress/server-side-render';
import {InspectorControls, useBlockProps} from '@wordpress/block-editor';
import {PanelBody, SelectControl} from "@wordpress/components";

registerBlockType('um-block/um-account', {
	edit: function (props) {
		let { tab, setAttributes } = props.attributes;
		const blockProps = useBlockProps();

		function get_options() {
			var option = [];

			option.push( { label: wp.i18n.__( 'All', 'wams' ), value: 'all' } );

			for ( var key in wams_account_settings ) {
				if ( wams_account_settings.hasOwnProperty( key ) && wams_account_settings[ key ]['enabled'] ) {
					option.push(
						{
							label: wams_account_settings[ key ]['label'],
							value: key
						}
					)
				}
			}

			return option;
		}

		function umShortcode( value ) {

			var shortcode = '[wams_account';

			if ( value !== 'all' ) {
				shortcode = shortcode + ' tab="' + value + '"';
			}

			shortcode = shortcode + ']';

			props.setAttributes({ content: shortcode });
		}

		return (
			<div {...blockProps}>
				<ServerSideRender block="um-block/um-account" attributes={props.attributes} />
				<InspectorControls>
					<PanelBody title={wp.i18n.__('Account Tab', 'wams')}>
						<SelectControl
							label={wp.i18n.__('Select Tab', 'wams')}
							className="wams_select_account_tab"
							value={tab}
							options={get_options()}
							style={{ height: '35px', lineHeight: '20px', padding: '0 7px' }}
							onChange={(value) => {
								props.setAttributes({ tab: value });
								umShortcode(value);
							}}
						/>
					</PanelBody>
				</InspectorControls>
			</div>
		);

	},
	save: function save(props) {
		return null;
	}
});

jQuery(window).on( 'load', function($) {
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {

			jQuery(mutation.addedNodes).find('.um.um-account').each(function() {
				var current_tab = jQuery(this).find('.um-account-main').attr('data-current_tab');

				if ( current_tab ) {
					jQuery(this).find('.um-account-tab[data-tab="'+current_tab+'"]').show();
					jQuery(this).find('.um-account-tab:not(:visible)').find( 'input, select, textarea' ).not( ':disabled' ).addClass('wams_account_inactive').prop( 'disabled', true ).attr( 'disabled', true );
					wams_responsive();
					// wams_modal_responsive();
				}
			});
		});
	});

	observer.observe(document, {attributes: false, childList: true, characterData: false, subtree:true});
});
