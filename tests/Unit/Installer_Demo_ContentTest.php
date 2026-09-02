<?php
/**
 * Level 1: telling the WordPress installer's demo content apart from real
 * content (D-02 / OWN-024, issue #10).
 *
 * The installer publishes «Hello world!» and «Sample Page» and drafts a
 * «Privacy Policy» page. In the local environment that demo post displaces
 * a real entry from the home «Del blog» section and from /blog. Staging and
 * production must never show it.
 *
 * The decision is pure: post type, slug, status and whether the object came
 * from the migration payload. Nothing here knows about post IDs — a site
 * that already holds production content must not be cleaned by deleting
 * 1, 2 and 3 blindly.
 *
 * @package Camino_Del_Dharma_Core
 */

use PHPUnit\Framework\TestCase;

/**
 * Behavior cluster: recognition of WordPress installer demo content.
 */
final class Installer_Demo_ContentTest extends TestCase {

	/**
	 * Protects the observable failure of D-02: the demo entry that reaches
	 * «Del blog» and /blog is recognised.
	 */
	public function test_hello_world_entry_is_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertTrue(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'post',
					'slug'      => 'hello-world',
					'status'    => 'publish',
				)
			)
		);
	}

	/**
	 * Protects the second published demo object: «Sample Page».
	 */
	public function test_sample_page_is_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertTrue(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'page',
					'slug'      => 'sample-page',
					'status'    => 'publish',
				)
			)
		);
	}

	/**
	 * Protects the third installer object: the Privacy Policy page, which
	 * the installer leaves as a draft.
	 */
	public function test_privacy_policy_draft_is_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertTrue(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'page',
					'slug'      => 'privacy-policy',
					'status'    => 'draft',
				)
			)
		);
	}

	/**
	 * Protects the Spanish install: Hostinger may install WordPress in
	 * Spanish, where the installer translates the default slugs. The
	 * demo content is the same content and must be recognised too.
	 *
	 * @dataProvider spanish_installer_defaults
	 *
	 * @param string $post_type Post type of the demo object.
	 * @param string $slug      Translated default slug.
	 * @param string $status    Status the installer leaves it in.
	 */
	public function test_spanish_installer_defaults_are_installer_demo( string $post_type, string $slug, string $status ) {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertTrue(
			$demo_content->is_installer_demo(
				array(
					'post_type' => $post_type,
					'slug'      => $slug,
					'status'    => $status,
				)
			)
		);
	}

	/**
	 * Translated defaults of a Spanish WordPress install.
	 */
	public function spanish_installer_defaults(): array {
		return array(
			'hola mundo'             => array( 'post', 'hola-mundo', 'publish' ),
			'pagina de ejemplo'      => array( 'page', 'pagina-de-ejemplo', 'publish' ),
			'politica de privacidad' => array( 'page', 'politica-de-privacidad', 'draft' ),
		);
	}

	/**
	 * Protects the real blog: the two production entries are content, not
	 * demo, and must survive every pass.
	 */
	public function test_real_blog_entry_is_not_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertFalse(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'post',
					'slug'      => 'sangha-refugio-hiperconexion',
					'status'    => 'publish',
				)
			)
		);
	}

	/**
	 * Protects the real /privacidad Page (ADR 0039/0041): a different
	 * object from the installer's Privacy Policy draft.
	 */
	public function test_published_privacidad_page_is_not_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertFalse(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'page',
					'slug'      => 'privacidad',
					'status'    => 'publish',
				)
			)
		);
	}

	/**
	 * Protects imported content above all else: an object carrying the
	 * importer's source key is production content (ADR 0033), whatever its
	 * slug happens to be.
	 */
	public function test_imported_object_is_never_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertFalse(
			$demo_content->is_installer_demo(
				array(
					'post_type'   => 'page',
					'slug'        => 'sample-page',
					'status'      => 'publish',
					'is_imported' => true,
				)
			)
		);
	}

	/**
	 * Protects the domain post types: an event or an author card that
	 * happens to use a default slug is first-party content.
	 */
	public function test_default_slug_on_a_domain_post_type_is_not_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertFalse(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'event',
					'slug'      => 'hello-world',
					'status'    => 'publish',
				)
			)
		);
	}

	/**
	 * Protects an object an editor already moved out of the installer's
	 * own states: only what the installer left behind is demo content.
	 */
	public function test_default_slug_in_an_editorial_status_is_not_installer_demo() {
		$demo_content = new Cdd_Core_Installer_Demo_Content();

		$this->assertFalse(
			$demo_content->is_installer_demo(
				array(
					'post_type' => 'page',
					'slug'      => 'sample-page',
					'status'    => 'private',
				)
			)
		);
	}
}
