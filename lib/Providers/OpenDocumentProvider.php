<?php

namespace OCA\Files_Confidential\Providers;

use OCA\Files_Confidential\Contract\IPolicy;
use OCA\Files_Confidential\Contract\IProvider;
use OCA\Files_Confidential\Model\AuthorizationCategory;
use OCA\Files_Confidential\Model\Policy;
use OCP\Files\File;
use Sabre\Xml\ParseException;
use Sabre\Xml\Reader;
use Sabre\Xml\Service;

class OpenDocumentProvider implements IProvider {
	const ELEMENT_DOCUMENT_META = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}document-meta';
	const ELEMENT_META = '{urn:oasis:names:tc:opendocument:xmlns:office:1.0}meta';
	const ELEMENT_USER_DEFINED = '{urn:oasis:names:tc:opendocument:xmlns:meta:1.0}user-defined';
	const ATTRIBUTE_NAME = '{urn:oasis:names:tc:opendocument:xmlns:meta:1.0}name';

	public function getSupportedMimeTypes(): array {
		return [
			'application/vnd.oasis.opendocument.presentation', // odp
			'application/vnd.oasis.opendocument.spreadsheet', // ods
			'application/vnd.oasis.opendocument.text', // odt
			'application/vnd.oasis.opendocument.text-template', // ott
			'application/vnd.oasis.opendocument.text-web', // oth
			'application/vnd.oasis.opendocument.text-master', // odm
			'application/vnd.oasis.opendocument.graphics', // odg
			'application/vnd.oasis.opendocument.graphics-template', // otg
			'application/vnd.oasis.opendocument.presentation', // odp
			'application/vnd.oasis.opendocument.presentation-template', // otp
			'application/vnd.oasis.opendocument.spreadsheet', // ods
			'application/vnd.oasis.opendocument.spreadsheet-template', // ots
			'application/vnd.oasis.opendocument.chart', // odc
			'application/vnd.oasis.opendocument.formula', // odf
			'application/vnd.oasis.opendocument.image', // odi
		];
	}

	/**
	 * @param \OCP\Files\File $file
	 * @return \OCA\Files_Confidential\Contract\IPolicy
	 */
	public function getPolicyForFile(File $file): ?IPolicy {
		$zipArchive = new \ZipArchive();
		if ($zipArchive->open($file->getInternalPath()) === false) {
			return null;
		}

		$xml = $zipArchive->getFromName('meta.xml');
		$zipArchive->close();

		$service = new Service();
		$service->elementMap = [
			self::ELEMENT_DOCUMENT_META => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				if ($children[0]['name'] !== self::ELEMENT_META) {
					return false;
				}
				return $children[0]['value'];
			},
			self::ELEMENT_META => function (Reader $reader) {
				$children = $reader->parseInnerTree();
				$props = [];
				foreach ($children as $child) {
					if (
						$child['name'] === self::ELEMENT_USER_DEFINED &&
						isset($child['attributes'][self::ATTRIBUTE_NAME])) {
						$props[] = [
							'key' => $child['attributes'][self::ATTRIBUTE_NAME],
							'value' => $child['value'],
						];
					}
				}
				return $props;
			}
		];

		try {
			$props = $service->parse($xml);
		} catch (ParseException $e) {
			// log
			return null;
		}

		return Policy::fromBAILS($props);
	}
}
