<?php
/*
 * @package     RadicalMart Uikit Package
 * @subpackage  tpl_radicalmart_uikit
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2022 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->set(InstallerScriptInterface::class, new class ($container->get(AdministratorApplication::class)) implements InstallerScriptInterface {
			/**
			 * The application object
			 *
			 * @var  AdministratorApplication
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected AdministratorApplication $app;

			/**
			 * The Database object.
			 *
			 * @var   DatabaseDriver
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected DatabaseDriver $db;

			/**
			 * Minimum Joomla version required to install the extension.
			 *
			 * @return  string
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected string $minimumJoomla = '4.2';

			/**
			 * Minimum PHP version required to install the extension.
			 *
			 * @return  string
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected string $minimumPhp = '7.4';

			/**
			 * Constructor.
			 *
			 * @param   AdministratorApplication  $app  The aplications obbject.
			 *
			 * @since __DEPLOY_VERSION__
			 */
			public function __construct(AdministratorApplication $app)
			{
				$this->app = $app;
				$this->db  = Factory::getContainer()->get('DatabaseDriver');
			}

			/**
			 * Function called after the extension is installed.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function install(InstallerAdapter $adapter): bool
			{
				return true;
			}

			/**
			 * Function called after the extension is updated.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function update(InstallerAdapter $adapter): bool
			{
				// Refresh media version
				(new Version())->refreshMediaVersion();

				return true;
			}

			/**
			 * Function called after the extension is uninstalled.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function uninstall(InstallerAdapter $adapter): bool
			{
				return true;
			}

			/**
			 * Function called before extension installation/update/removal procedure commences.
			 *
			 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function preflight(string $type, InstallerAdapter $adapter): bool
			{
				if ($type === 'update')
				{
					// Remove old files
					$path = Path::clean(JPATH_ROOT . '/templates/system/radicalmart_uikit');
					if (Folder::exists($path))
					{
						Folder::delete($path);
					}
				}

				return true;
			}

			/**
			 * Function called after extension installation/update/removal procedure commences.
			 *
			 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function postflight(string $type, InstallerAdapter $adapter): bool
			{
				$this->app->getLanguage()->load('tpl_radicalmart_uikit', JPATH_SITE);

				// Get template
				$template  = null;
				$templates = $this->app->bootComponent('templates')->getMVCFactory()
					->createModel('Style', 'Administrator')->getSiteTemplates();

				foreach ($templates as $item)
				{
					if ((int) $item->home)
					{
						$template = (!empty($item->parent)) ? $item->parent : $item->template;

						break;
					}
				}

				$root_src   = Path::clean(JPATH_ROOT . '/templates/system/radicalmart_uikit');
				$root_dest  = Path::clean(JPATH_ROOT . '/templates/' . $template);
				$media_src  = Path::clean(JPATH_ROOT . '/templates/system/radicalmart_uikit/media');
				$media_dest = Path::clean(JPATH_ROOT . '/media/templates/site/' . $template);
				$sources    = Folder::files($root_src, '.', true, true);

				$overrideFiles = [];
				$copyFiles     = [];
				foreach ($sources as $src)
				{
					$files = [];
					if (strpos($src, $media_src) !== false)
					{
						$dest    = Path::clean(str_replace($media_src, $media_dest, $src));
						$files[] = [
							'src'  => Path::clean($src),
							'dest' => $dest,
							'type' => 'file',
						];

						$dest    = Path::clean(str_replace($media_src, $root_dest, $src));
						$files[] = [
							'src'  => Path::clean($src),
							'dest' => $dest,
							'type' => 'file',
						];
					}
					else
					{
						$dest    = Path::clean(str_replace($root_src, $root_dest, $src));
						$files[] = [
							'src'  => Path::clean($src),
							'dest' => $dest,
							'type' => 'file',
						];
					}

					foreach ($files as $file)
					{
						if (basename($file['dest']) !== $file['dest'])
						{
							$newdir = dirname($file['dest']);
							if (!Folder::create($newdir))
							{
								Log::add(Text::sprintf('JLIB_INSTALLER_ERROR_CREATE_DIRECTORY', $newdir), Log::WARNING, 'jerror');

								return false;
							}
						}

						if (File::exists($file['dest']))
						{
							$overrideFiles[] = '<code>' .
								str_replace(JPATH_ROOT . '/','/', $file['dest']) . '</code>';
						}

						$copyFiles[] = $file;
					}
				}

				$result = $adapter->getParent()->copyFiles($copyFiles, true);
				if ($result)
				{
					$this->app->enqueueMessage(Text::sprintf('TPL_RADICALMART_UIKIT_INSTALL_COPY_FILES_TO',
						'/templates/' . $template));
					if (!empty($overrideFiles))
					{
						asort($overrideFiles);
						$this->app->enqueueMessage(Text::sprintf('TPL_RADICALMART_UIKIT_INSTALL_OVERRIDE_FILES',
							implode('<br>', $overrideFiles)), 'warning');
					}
				}
				else
				{
					$this->app->enqueueMessage(Text::_('TPL_RADICALMART_UIKIT_INSTALL_ERROR_COPY_FILES'));
				}

				return $result;
			}
		});
	}
};