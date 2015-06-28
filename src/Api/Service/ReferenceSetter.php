<?php

namespace Wikibase\Api\Service;

use Mediawiki\Api\MediawikiApi;
use Mediawiki\Api\SimpleRequest;
use UnexpectedValueException;
use Wikibase\DataModel\Reference;
use Wikibase\DataModel\Serializers\ReferenceSerializer;
use Wikibase\DataModel\Statement\Statement;
use Wikibase\DataModel\Statement\StatementGuid;

/**
 * @author Adam Shorland
 */
class ReferenceSetter {

	/**
	 * @var MediawikiApi
	 */
	private $api;

	/**
	 * @var ReferenceSerializer
	 */
	private $referenceSerializer;

	/**
	 * @param MediawikiApi $api
	 * @param ReferenceSerializer $referenceSerializer
	 */
	public function __construct( MediawikiApi $api, ReferenceSerializer $referenceSerializer ) {
		$this->api = $api;
		$this->referenceSerializer = $referenceSerializer;
	}

	/**
	 * @since 0.2
	 *
	 * @param Reference $reference new reference value
	 * @param Statement|StatementGuid|string $statement Statement object or GUID which has the reference
	 * @param Reference|string $targetReference target (old) reference of hash
	 *
	 * @return bool
	 * @throws UnexpectedValueException
	 */
	public function set( Reference $reference, $statement, $targetReference = null ) {
		if( is_string( $statement ) ) {
			$guid = $statement;
		} else if ( $statement instanceof StatementGuid ) {
			$guid = $statement->getSerialization();
		} else if ( $statement instanceof Statement ) {
			$guid = $statement->getGuid();
		} else {
			throw new UnexpectedValueException( 'Could not get statement guid from $target' );
		}
		if( !is_string( $guid ) ) {
			throw new UnexpectedValueException( 'Unexpected statement guid got from $target' );
		}

		$params = array(
			'statement' => $guid,
			'snaks' => $this->referenceSerializer->serialize( $reference )
		);

		if( !is_null( $targetReference ) ) {
			if( $targetReference instanceof Reference ) {
				$targetReference = $reference->getHash();
			}
			if( !is_string( $targetReference ) ) {
				throw new UnexpectedValueException( 'Could not get reference hash from $targetReference' );
			}
			$params['reference'] = $targetReference;
		}

		$params['token'] = $this->api->getToken();
		$this->api->postRequest( new SimpleRequest( 'wbsetreference', $params ) );
		return true;
	}

} 