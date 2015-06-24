<?php
/**
 * Hooks for Hreflang extension
 *
 * @file
 * @ingroup Extensions
 */

class HreflangHooks {


	public static function onBeforePageDisplay( OutputPage $out, SkinTemplate $sk ) {
		$config = $out->getConfig();
		if ( self::canOutputHreflang( $config ) ) {
			# Generate hreflang tags
			$languageLinks = $out->getLanguageLinks();
			if ( empty($languageLinks) ) {
				// shortcut - if we don't have any language links, don't bother
				return;
			}
			$addedLink = false;
			$pages = $config->get("HreflangPages");
			if( !$pages ) {
				$pages = array();
				$foundPage = true;
			} else {
				$pages = array_flip( $pages );
				$pageName = $out->getLanguage()->getHtmlCode() . ":" . $out->getTitle()->getBaseText();
				$foundPage = isset( $pages[$pageName] );
			}
			foreach ( $languageLinks as $languageLinkText ) {
				$languageLinkTitle = Title::newFromText( $languageLinkText );
				if ( !$languageLinkTitle ) {
					continue;
				}
				$ilInterwikiCode = $languageLinkTitle->getInterwiki();
				if ( !Language::isKnownLanguageTag( $ilInterwikiCode ) ) {
					continue;
				}
				$foundPage = $foundPage || isset( $pages[$languageLinkText] );
				$tags[] = Html::element( 'link', array(
					'rel' => 'alternate',
					'hreflang' => wfBCP47( $ilInterwikiCode ),
					'href' => $languageLinkTitle->getFullURL()
				) );
				$addedLink = true;
			}
			// Only add current language link if we had any other links
			if ( $addedLink ) {
				$tags[] = Html::element( 'link', array(
					'rel' => 'alternate',
					'hreflang' => $out->getLanguage()->getHtmlCode(),
					'href' => $out->getTitle()->getFullURL()
				) );

			}
		}
		if ( $foundPage && $tags ) {
			$out->addHeadItem("hreflang:tags", join("\n", $tags));
		}
	}

	/**
	 * Are we supposed to output hreflang headers?
	 * @param Config $config
	 * @return boolean
	 */
	protected static function canOutputHreflang( Config $config ) {
 		return $config->get( 'HreflangGenerate' ) !== false
 		;
// 		&& $this->getProperty( 'enableHreflangLinks' ) !== false;
	}

}
