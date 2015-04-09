<?php

namespace WorldDirect\JsonApi\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 Ben Walch <bwa@world-direct.at>, World-Direct eBusiness Solutions Gesellschaft m.b.H.
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * @author		Ben Walch <ben.walch@world-direct.at>
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */

use \TYPO3\CMS\Core\Utility\GeneralUtility;
use \TYPO3\CMS\Core\Utility\ClassNamingUtility;
 
class ApiController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * @var \TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface
	 * @inject
	 */
	protected $persistenceManager;
	
	/**
	 * @var TYPO3\CMS\Core\Resource\StorageRepository
	 * @inject
	 */
	protected $storageRepository;
	
	/**
	 * @var string
	 */
	protected $defaultViewObjectName = 'TYPO3\CMS\Extbase\Mvc\View\JsonView';
	
	
	/**
	 * @var array
	 */
	protected $defaultViewConfiguration = array(
		'value' => array(
			'_descendAll' => array(
				
			)
		)
	);
	
	/**
	 * initializeList
	 *
	 */
	public function initializeAction() {
		
		$path = GeneralUtility::_GP('path');
		$directory = GeneralUtility::_GP('directory');
		$storagePages = GeneralUtility::_GP('storagePages');
		$limit = intval(GeneralUtility::_GP('limit'));
		$orderBy = GeneralUtility::_GP('orderBy');
		$orderDirection = GeneralUtility::_GP('orderDirection');
		
		$this->settings['directory'] = isset($directory) ? $directory : $this->settings['directory'];
		$this->settings['list']['storagePages'] = isset($storagePages) ? GeneralUtility::trimExplode(',', $storagePages) : $this->settings['list']['storagePages'];
		$this->settings['list']['limit'] = $limit > 0 ? $limit : $this->settings['list']['limit'];
		$this->settings['list']['orderBy'] = isset($orderBy) ? $orderBy : $this->settings['list']['orderBy'];
		$this->settings['list']['orderDirection'] = isset($orderDirection) ? $orderDirection : $this->settings['list']['orderDirection'];
		
		$this->settings['output']['config'] = $this->fixOutputConfig($this->settings['output']['config']);
		
	}
	
	/**
     * action list
     *
     * @param string $path
     * @return string json
     *
     */
	public function listAction($path) {
		
		$modelClass = '';
		$data = array();
		
		if (strtoupper($path) == 'FILES') {
			$modelClass = 'TYPO3\CMS\Core\Resource\File';
			$folder = $this->getTargetFolder();
			$data = $folder->getFiles();
			if (isset($this->settings['list']['limit'])) {
				$data = array_slice($data, 0, $this->settings['list']['limit']);
			}
		} else {
			$modelClass = $this->resolvePathToModelClass($path);
			
			$query = $this->persistenceManager->createQueryForType($modelClass);
			
			if (isset($this->settings['list']['storagePages'])) {
				$query->getQuerySettings()->setStoragePageIds($this->settings['list']['storagePages']);
			} else {
				$query->getQuerySettings()->setRespectStoragePage(FALSE);
			}
			if (isset($this->settings['list']['limit'])) {
				$query->setLimit($this->settings['list']['limit']);
			}
			$query->setOrderings(array($this->settings['list']['orderBy'] => strtoupper($this->settings['list']['orderDirection'])));
			
			$data = $query->execute();
		}
		
		$this->view->setVariablesToRender(array($modelClass));
		$this->view->setConfiguration(array_merge($this->defaultViewConfiguration, $this->settings['output']['config']));
		$this->view->assign($modelClass, $data);
		
	}
	
	/**
     * action single
     *
     * @param string $path
     * @param int $uid
     * @return string json
     *
     */
	public function singleAction($path, $uid) {
		if (isset($uid)) {
			$modelClass = $this->resolvePathToModelClass($path);
			
			$query = $this->persistenceManager->createQueryForType($modelClass);

            $query->getQuerySettings()->setRespectStoragePage(FALSE);
            $query->getQuerySettings()->setRespectSysLanguage(FALSE);
            $query->matching($query->equals('uid', $uid));
			
			$this->view->setVariablesToRender(array($modelClass));
			$this->view->setConfiguration(array_merge($this->defaultViewConfiguration, $this->settings['output']['config']));
			$this->view->assign($modelClass, $query->execute()->getFirst());
		}
	}
	
	/**
	 * Returns a string with the correct model class
	 *
	 * @param string $path
	 * @return string
	 */
	protected function resolvePathToModelClass($path) {
		$parts = explode('-', $path);
		if (count($parts) < 3) {
			array_unshift($parts, '');
		}
		list($vendor, $extKey, $model) = $parts;
		
		$extension = GeneralUtility::underscoredToUpperCamelCase($extKey);
		$model = GeneralUtility::underscoredToUpperCamelCase($model);
		
		$modelClass = ($vendor ? $vendor . '\\' : '') . $extension . '\\Domain\\Model\\' . $model;
		if (empty($vendor)) {
			$modelClass = 'Tx_' . $extension . '_Domain_Model_' . $model;
		}
		return $modelClass;
	}
	
	/**
	 * returns the target folder
	 *
	 * @return \TYPO3\CMS\Core\Resource\ResourceStorage
	 */
	protected function getTargetFolder() {
		$storages = $this->storageRepository->findAll();
		
		foreach ($storages as $storage) {
			$storageConfig = $storage->getConfiguration();
			if (strpos($this->settings['directory'], $storageConfig['basePath']) !== FALSE) {
				return $storage->getFolder(str_replace($storageConfig['basePath'], '', $this->settings['directory']));
			}
		}
		return NULL;
	}
	
	
	protected function fixOutputConfig($config, $level = 0) {
		foreach ($config as $key => $value) {
			if ($key == '_only') {
				$config[$key] = GeneralUtility::trimExplode(',', $value);
			} else if (is_array($value)) {
				$config[$key] = $this->fixOutputConfig($value, $level+1);
			}
			if ($level == 0) {
				$config[$key]['_descendAll'] = $config[$key];
			}
		}
		return $config;
	}
	
}

?>