<?php

declare(strict_types=1);

/*
 * Copyright (c) 2022 The Recognize contributors.
 * This file is licensed under the Affero General Public License version 3 or later. See the COPYING file.
 */

namespace OCA\Files_Confidential\Controller;

use OCA\Files_Confidential\Service\BafService;
use OCA\Files_Confidential\Service\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\IL10N;
use OCP\IRequest;
use Safe\Exceptions\JsonException;

class AdminController extends Controller {
	private SettingsService $settingsService;
	private IL10N $l10n;
	private BafService $bafService;
	private IAppData $appData;

	public function __construct(
		string $appName,
		IRequest $request,
		SettingsService $settingsService,
		IL10N $l10n,
		BafService $bafService,
		IAppData $appData
	) {
		parent::__construct($appName, $request);
		$this->settingsService = $settingsService;
		$this->l10n = $l10n;
		$this->bafService = $bafService;
		$this->appData = $appData;
	}

	/**
	 * @param array $value
	 * @return \OCP\AppFramework\Http\JSONResponse
	 */
	public function setClassificationLabels(array $value): JSONResponse {
		try {
			$this->settingsService->setClassificationLabels($value);
			return new JSONResponse([], Http::STATUS_OK);
		} catch (\Exception $e) {
			return new JSONResponse([], Http::STATUS_BAD_REQUEST);
		}
	}

	public function getClassificationLabels(): JSONResponse {
		$labels = $this->settingsService->getClassificationLabels();
		$labels = array_map(static fn ($label):array => $label->toArray(), $labels);
		return new JSONResponse($labels);
	}

	/**
	 * @return \OCP\AppFramework\Http\JSONResponse
	 * @throws \OCP\Files\NotPermittedException
	 */
	public function importBaf() : JSONResponse {
		$upload = $this->request->getUploadedFile('baf');
		$result = [];
		if ($upload['type'] !== 'text/xml') {
			$result['errors'][] = $this->l10n->t('Unsupported file type for import. XML file is expected.');
			return new JSONResponse(['status' => 'error', 'data' => $result['errors']]);
		}

		$xml = file_get_contents($upload['tmp_name']);
		if ($xml === false) {
			$result['errors'][] = $this->l10n->t('Could not read uploaded file');
			return new JSONResponse(['status' => 'error', 'data' => $result['errors']]);
		}

		$labels = $this->bafService->parseXml($xml);
		try {
			$this->settingsService->setClassificationLabels(array_map(static fn ($label):array => $label->toArray(), $labels));
		} catch (JsonException $e) {
			$result['errors'][] = $this->l10n->t('Could not store extracted labels');
			return new JSONResponse(['status' => 'error', 'data' => $result['errors']]);
		}

		try {
			$folder = $this->appData->getFolder('files_confidential');
		} catch (NotFoundException $e) {
			$folder = $this->appData->newFolder('files_confidential');
		}
		$folder->newFile('baf.xml', $xml);

		return new JSONResponse([]);
	}
}
