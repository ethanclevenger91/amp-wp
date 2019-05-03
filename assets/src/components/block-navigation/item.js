/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import { Button, Draggable, DropZone } from '@wordpress/components';
import { Fragment, Component } from '@wordpress/element';
import { withDispatch, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BlockPreviewLabel } from '../';
import { __ } from '@wordpress/i18n';

/**
 * Parses drag & drop events to ensure the event contains valid transfer data.
 *
 * @param {Object} event
 * @return {Object} Parsed event data.
 */
const parseDropEvent = ( event ) => {
	let result = {
		srcClientId: null,
		srcIndex: null,
		type: null,
	};

	if ( ! event.dataTransfer ) {
		return result;
	}

	try {
		result = Object.assign( result, JSON.parse( event.dataTransfer.getData( 'text' ) ) );
	} catch ( err ) {
		return result;
	}

	return result;
};

class BlockNavigationItem extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			isDragging: false,
		};

		this.onDrop = this.onDrop.bind( this );
	}

	getInsertIndex( position ) {
		const { index } = this.props;

		if ( index !== undefined ) {
			return position.y === 'top' ? index : index + 1;
		}
	}

	onDrop( event, position ) {
		const { block: { clientId }, moveBlockToPosition, index } = this.props;
		const { srcClientId, srcIndex, type } = parseDropEvent( event );

		const isBlockDropType = ( dropType ) => dropType === 'block';
		const isSameBlock = ( src, dst ) => src === dst;

		if ( ! isBlockDropType( type ) || isSameBlock( srcClientId, clientId ) ) {
			return;
		}

		const positionIndex = this.getInsertIndex( position );
		const insertIndex = srcIndex < index ? positionIndex - 1 : positionIndex;
		moveBlockToPosition( srcClientId, insertIndex );
	}

	render() {
		const { block, index, isSelected, onClick } = this.props;
		const { clientId } = block;
		const blockElementId = `block-navigation-item-${ clientId }`;
		const transferData = {
			type: 'block',
			srcIndex: index,
			srcClientId: clientId,
		};

		return (
			<div className="editor-block-navigation__item block-editor-block-navigation__item">
				<Draggable
					elementId={ blockElementId }
					transferData={ transferData }
					onDragStart={ () => this.setState( { isDragging: true } ) }
					onDragEnd={ () => this.setState( { isDragging: false } ) }
				>
					{
						( { onDraggableStart, onDraggableEnd } ) => (
							<Fragment>
								<DropZone
									className={ this.state.isDragging ? 'is-dragging-block' : undefined }
									onDrop={ this.onDrop }
								/>
								<Button
									className={ classnames(
										'components-button editor-block-navigation__item-button block-editor-block-navigation__item-button',
										{
											'is-selected': isSelected,
										}
									) }
									onClick={ onClick }
									id={ blockElementId }
									onDragStart={ onDraggableStart }
									onDragEnd={ onDraggableEnd }
									draggable
								>
									<BlockPreviewLabel
										block={ block }
										accessibilityText={ isSelected && __( '(selected block)', 'amp' ) }
									/>
								</Button>
							</Fragment>
						)
					}
				</Draggable>
			</div>
		);
	}
}

const applyWithSelect = withSelect( ( select, { block: { clientId } } ) => {
	const { getBlockIndex, getBlockRootClientId } = select( 'core/block-editor' );

	return {
		index: getBlockIndex( clientId, getBlockRootClientId( clientId ) ),
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, { block: { clientId } }, { select } ) => {
	const { getBlockOrder, getBlockRootClientId } = select( 'core/block-editor' );
	const { moveBlockToPosition } = dispatch( 'core/block-editor' );

	const rootClientId = getBlockRootClientId( clientId );
	const blockOrder = getBlockOrder( rootClientId );

	return {
		moveBlockToPosition: ( block, index ) => {
			// Since the BlockNavigation list is reversed, inserting at index 0 actually means inserting at the end, and vice-versa.
			const reversedIndex = blockOrder.length - 1 - index;

			moveBlockToPosition( block, rootClientId, rootClientId, reversedIndex );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( BlockNavigationItem );
