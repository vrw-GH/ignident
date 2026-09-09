/**
 * Internal dependencies
 */
import DimensionsControl from '../components/dimentions-control';

/**
 * WordPress dependencies
 */

import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { 
    SelectControl, 
    RangeControl, 
    ToggleControl, 
    PanelBody,
    ColorPalette,
	FontSizePicker,
	PanelRow,
	ButtonGroup,
	Button
} from '@wordpress/components';

import apiFetch from '@wordpress/api-fetch';
const { addQueryArgs } = wp.url;
class Inspector extends Component {

	constructor( props ) {
		super( props );
		this.state = {
			contactFormPosts: [],
			device:'desktop',
		};
	};

	componentDidMount() {
		this.fetchContactFormPosts();
	};

	fetchContactFormPosts(){
		var queryAttrData = {wpnonce: htcontactdata.security};
		var query = addQueryArgs( '/htcontactform/v1/posts/ht-form', queryAttrData );

		apiFetch( { path: query } )
		  .then( data => {
		  	var postlist = [{ label: 'Select', value: '', disabled: true }];
		  	for(var key in data){
		  		postlist.push({label: data[key], value: key });
		  	}
		  	this.setState( { contactFormPosts: postlist } );
		  });
	}

	render() {

		const { contactFormPosts } = this.state;

		const {
			className,
            attributes,
            setAttributes
        } = this.props;

        const { 
        	formId,
			inputBorderRadius,
			inputPadding,
			borderType,
			areaMargin,
			inputMargin,
			blockUniqId,
			buttonPadding,
			buttonMargin,
			btnBorderType,
			buttonBorderRadius,
			labelFontSize,
			btnFontSize,
			inputTextSize,
			inputHight,
			textAreaHight
		} = attributes;

        const colorSelectorStyles = {
            selectedColorDisplay: {
                width: 30,
                height: 12,
                display: "inline-block",
                marginLeft: 10,
                verticalAlign: "middle",
            },
        };

		const handleTabs = ( event, stateKey, value ) => {
			this.setState({ [ stateKey ]: value });
		};

		const onChangeResponsiveData = ( newValue, device, dataKey ) => {
			const newDimensions = { ...attributes[dataKey] };
            newDimensions[device] = newValue;
			setAttributes( { [ dataKey ]: newDimensions } );
		};

        const formStyle = `
						   #ht-editor-bock-${blockUniqId}{
							   margin: ${areaMargin.top}${areaMargin.unit} ${areaMargin.right}${areaMargin.unit} ${areaMargin.bottom}${areaMargin.unit} ${areaMargin.left}${areaMargin.unit};
						   }
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=text],
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=email],
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=password],
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=search],
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=tel],
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=url],
						   #ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 select{
								height: ${inputHight.desktop}px;
						}

						#ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input:not([type=checkbox]):not([type=submit]),
						#ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 textarea{
							font-size: ${attributes.inputTextSize.desktop};
							background:${attributes.inputBackground};
							color: ${attributes.inputTextColor};
							Padding: ${inputPadding.top}${inputPadding.unit} ${inputPadding.right}${inputPadding.unit} ${inputPadding.bottom}${inputPadding.unit} ${inputPadding.left}${inputPadding.unit};
							margin: ${inputMargin.top}${inputMargin.unit} ${inputMargin.right}${inputMargin.unit} ${inputMargin.bottom}${inputMargin.unit} ${inputMargin.left}${inputMargin.unit};
							border: ${attributes.borderWidth}px ${borderType} ${attributes.borderColor};
							border-radius: ${inputBorderRadius.top}${inputBorderRadius.unit} ${inputBorderRadius.right}${inputBorderRadius.unit} ${inputBorderRadius.bottom}${inputBorderRadius.unit} ${inputBorderRadius.left}${inputBorderRadius.unit};
						}

						#ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 textarea{
							height: ${textAreaHight.desktop}px;
						}
						#ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 label{
							font-size: ${labelFontSize.desktop};
							color: ${attributes.labelColor};
						}
						#ht-editor-bock-${blockUniqId} #wpcf7-f${formId}-o1.wpcf7 input[type=submit]{
							font-size: ${btnFontSize.desktop};
							Padding: ${buttonPadding.desktop.top}${buttonPadding.unit} ${buttonPadding.desktop.right}${buttonPadding.unit} ${buttonPadding.desktop.bottom}${buttonPadding.unit} ${buttonPadding.desktop.left}${buttonPadding.unit};
							margin: ${buttonMargin.desktop.top}${buttonMargin.unit} ${buttonMargin.desktop.right}${buttonMargin.unit} ${buttonMargin.desktop.bottom}${buttonMargin.unit} ${buttonMargin.desktop.left}${buttonMargin.unit};
							color: ${attributes.btnTextColor};
							border: ${attributes.btnBorderWidth}px ${btnBorderType} ${attributes.btnBorderColor};
							border-radius: ${buttonBorderRadius.top}${buttonBorderRadius.unit} ${buttonBorderRadius.right}${buttonBorderRadius.unit} ${buttonBorderRadius.bottom}${buttonBorderRadius.unit} ${buttonBorderRadius.left}${buttonBorderRadius.unit};
							background:${attributes.btnBackgroundColor};
						}`;

        return (
        	<Fragment>
	            <InspectorControls>
		            <PanelBody	title={ __( "Settings", 'ht-contactform' ) } initialOpen={ true }>
		            	<SelectControl
							label={ __( 'Select Form', 'ht-contactform' ) }
							value={formId}
							options={ contactFormPosts }
							onChange={ ( newId ) => setAttributes( { formId: newId } ) }
						/>
		            </PanelBody>
		            {/* <PanelBody	title={ __( "Style", 'ht-contactform' ) } initialOpen={ false }>
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Area','ht-contactform') }</h2>
						<DimensionsControl 
                            lavel = { __('Margin','ht-contactform') }
                            dimensions = { areaMargin }
                            attributesKey = { 'areaMargin' }
                            setAttributes = { setAttributes }
                        />
		            	<h2 className={'ht-contactform-blocks-section-title'}>{ __('Border','ht-contactform') }</h2>
		            	<SelectControl
							label={ __( 'Border Type', 'ht-contactform' ) }
							value={borderType}
							options={ [
						        { value: 'none',   label: 'None'   },
						        { value: 'solid',  label: 'Solid'  },
						        { value: 'double', label: 'Double' },
						        { value: 'dotted', label: 'Dotted' },
						        { value: 'dashed', label: 'Dashed' },
						        { value: 'groove', label: 'Groove' },
						    ] }
							onChange={ ( newType ) => setAttributes( { borderType: newType } ) }
						/>
						{ borderType !== 'none'?(<RangeControl
                            label={ __( 'Border Width', 'ht-contactform' ) }
                            value={ attributes.borderWidth }
                            onChange={ ( newValue ) => setAttributes( { borderWidth: newValue } ) }
                            min={ 0 }
                            step={ 1 }
                            max={ 10 }
                        />):''}
						<DimensionsControl 
                            lavel = { __('Border Radius','ht-contactform') }
                            dimensions = { inputBorderRadius }
                            attributesKey = { 'inputBorderRadius' }
                            setAttributes = { setAttributes }
                        />
						{ borderType !== 'none'? (
                        	<div>
	                        	<h3 style={{marginTop:15}}>
		                            {__("Border Color", "ht-contactform")}
		                            <span
		                                style={{
		                                    ...colorSelectorStyles.selectedColorDisplay,
		                                    backgroundColor:attributes.borderColor
		                                }}
		                            ></span>
		                        </h3>
		                        <ColorPalette
		                            colors={ [
		                                { name: 'Black', color: '#000000' },
		                                { name: 'Orange', color: '#FF6900' },
		                                { name: 'Vivid Red', color: '#CF2E2E' },
		                                { name: 'Pink', color: '#F78DA7' },
		                                { name: 'White', color: '#FFFFFF' },
		                                { name: 'Blue', color: '#8ED1FC' },
		                            ] }
		                            value={attributes.borderColor}
		                            onChange={ ( newColor ) => setAttributes( { borderColor: newColor } ) }
		                        />
	                        </div>
                        ):''}
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Field','ht-contactform') }</h2>
                        <DimensionsControl 
                            lavel = { __('Padding','ht-contactform') }
                            dimensions = { inputPadding }
                            attributesKey = { 'inputPadding' }
                            setAttributes = { setAttributes }
                        />
                        <DimensionsControl 
                            lavel = { __('Margin','ht-contactform') }
                            dimensions = { inputMargin }
                            attributesKey = { 'inputMargin' }
                            setAttributes = { setAttributes }
                        />
						<h3 style={{marginTop:15}}>
                            {__("Background Color", "ht-contactform")}
                            <span
                                style={{
                                    ...colorSelectorStyles.selectedColorDisplay,
                                    backgroundColor:attributes.inputBackground
                                }}
                            ></span>
                        </h3>
                        <ColorPalette
                            colors={ [
                                { name: 'Black', color: '#000000' },
                                { name: 'Orange', color: '#FF6900' },
                                { name: 'Vivid Red', color: '#CF2E2E' },
                                { name: 'Pink', color: '#F78DA7' },
                                { name: 'White', color: '#FFFFFF' },
                                { name: 'Blue', color: '#8ED1FC' },
                            ] }
                            value={attributes.inputBackground}
                            onChange={ ( newColor ) => setAttributes( { inputBackground: newColor } ) }
                        />
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Field Text','ht-contactform') }</h2>
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Font Size', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<FontSizePicker
							fontSizes={ [
								{
									name: __( 'Small','ht-contactform' ),
									slug: 'small',
									size: '12px',
								},
								{
									name: __( 'Medium','ht-contactform' ),
									slug: 'medium',
									size: '18px',
								},
								{
									name: __( 'Large','ht-contactform' ),
									slug: 'large',
									size: '24px',
								}
							] }
							value={inputTextSize[this.state.device]}
							fallbackFontSize={ inputTextSize.desktop }
							onChange={ ( newValue ) => onChangeResponsiveData( newValue, this.state.device, 'inputTextSize' ) }
						/>
                        <h3 style={{marginTop:15}}>
                            {__("Text Color", "ht-contactform")}
                            <span
                                style={{
                                    ...colorSelectorStyles.selectedColorDisplay,
                                    backgroundColor:attributes.inputTextColor
                                }}
                            ></span>
                        </h3>
                        <ColorPalette
                            colors={ [
                                { name: 'Black', color: '#000000' },
                                { name: 'Orange', color: '#FF6900' },
                                { name: 'Vivid Red', color: '#CF2E2E' },
                                { name: 'Pink', color: '#F78DA7' },
                                { name: 'White', color: '#FFFFFF' },
                                { name: 'Blue', color: '#8ED1FC' },
                            ] }
                            value={attributes.inputTextColor}
                            onChange={ ( newColor ) => setAttributes( { inputTextColor: newColor } ) }
                        />
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Input','ht-contactform') }</h2>
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Height', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<RangeControl
							label="Height"
							value={ inputHight[this.state.device] }
							onChange={ ( newValue ) => onChangeResponsiveData( newValue, this.state.device, 'inputHight' ) }
							min={ 1 }
							step={ 1 }
							max={ 150 }
						/>
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Textarea','ht-contactform') }</h2>
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Height', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<RangeControl
							label="Height"
							value={ textAreaHight[this.state.device] }
							onChange={ ( newValue ) => onChangeResponsiveData( newValue, this.state.device, 'textAreaHight' ) }
							min={ 80 }
							step={ 2 }
							max={ 300 }
						/>
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Label','ht-contactform') }</h2>
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Font Size', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<FontSizePicker
							fontSizes={ [
								{
									name: __( 'Small','ht-contactform' ),
									slug: 'small',
									size: '12px',
								},
								{
									name: __( 'Medium','ht-contactform' ),
									slug: 'medium',
									size: '18px',
								},
								{
									name: __( 'Large','ht-contactform' ),
									slug: 'large',
									size: '24px',
								}
							] }
							value={labelFontSize[this.state.device]}
							fallbackFontSize={ labelFontSize.desktop }
							onChange={ ( newValue ) => onChangeResponsiveData( newValue, this.state.device, 'labelFontSize' ) }
						/>
						<h3 style={{marginTop:15}}>
                            {__("Label Color", "ht-contactform")}
                            <span
                                style={{
                                    ...colorSelectorStyles.selectedColorDisplay,
                                    backgroundColor:attributes.labelColor
                                }}
                            ></span>
                        </h3>
                        <ColorPalette
                            colors={ [
                                { name: 'Black', color: '#000000' },
                                { name: 'Orange', color: '#FF6900' },
                                { name: 'Vivid Red', color: '#CF2E2E' },
                                { name: 'Pink', color: '#F78DA7' },
                                { name: 'White', color: '#FFFFFF' },
                                { name: 'Blue', color: '#8ED1FC' },
                            ] }
                            value={attributes.labelColor}
                            onChange={ ( newColor ) => setAttributes( { labelColor: newColor } ) }
                        />
						<h2 className={'ht-contactform-blocks-section-title'}>{ __('Button','ht-contactform') }</h2>
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Font Size', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<FontSizePicker
							fontSizes={ [
								{
									name: __( 'Small','ht-contactform' ),
									slug: 'small',
									size: '12px',
								},
								{
									name: __( 'Medium','ht-contactform' ),
									slug: 'medium',
									size: '18px',
								},
								{
									name: __( 'Large','ht-contactform' ),
									slug: 'large',
									size: '24px',
								}
							] }
							value={btnFontSize[this.state.device]}
							fallbackFontSize={ btnFontSize.desktop }
							onChange={ ( newValue ) => onChangeResponsiveData( newValue, this.state.device, 'btnFontSize' ) }
						/>
						<SelectControl
							label={ __( 'Border Type', 'ht-contactform' ) }
							value={btnBorderType}
							options={ [
						        { value: 'none',   label: 'None'   },
						        { value: 'solid',  label: 'Solid'  },
						        { value: 'double', label: 'Double' },
						        { value: 'dotted', label: 'Dotted' },
						        { value: 'dashed', label: 'Dashed' },
						        { value: 'groove', label: 'Groove' },
						    ] }
							onChange={ ( newType ) => setAttributes( { btnBorderType: newType } ) }
						/>
						{ btnBorderType !== 'none'?(<RangeControl
                            label={ __( 'Border Width', 'ht-contactform' ) }
                            value={ attributes.btnBorderWidth }
                            onChange={ ( newValue ) => setAttributes( { btnBorderWidth: newValue } ) }
                            min={ 0 }
                            step={ 1 }
                            max={ 10 }
                        />):''}
						{ btnBorderType !== 'none'? (
                        	<div>
	                        	<h3 style={{marginTop:15}}>
		                            {__("Border Color", "ht-contactform")}
		                            <span
		                                style={{
		                                    ...colorSelectorStyles.selectedColorDisplay,
		                                    backgroundColor:attributes.btnBorderColor
		                                }}
		                            ></span>
		                        </h3>
		                        <ColorPalette
		                            colors={ [
		                                { name: 'Black', color: '#000000' },
		                                { name: 'Orange', color: '#FF6900' },
		                                { name: 'Vivid Red', color: '#CF2E2E' },
		                                { name: 'Pink', color: '#F78DA7' },
		                                { name: 'White', color: '#FFFFFF' },
		                                { name: 'Blue', color: '#8ED1FC' },
		                            ] }
		                            value={attributes.btnBorderColor}
		                            onChange={ ( newColor ) => setAttributes( { btnBorderColor: newColor } ) }
		                        />
	                        </div>
                        ):''}
						<h3 style={{marginTop:15}}>
                            {__("Text Color", "ht-contactform")}
                            <span
                                style={{
                                    ...colorSelectorStyles.selectedColorDisplay,
                                    backgroundColor:attributes.btnTextColor
                                }}
                            ></span>
                        </h3>
                        <ColorPalette
                            colors={ [
                                { name: 'Black', color: '#000000' },
                                { name: 'Orange', color: '#FF6900' },
                                { name: 'Vivid Red', color: '#CF2E2E' },
                                { name: 'Pink', color: '#F78DA7' },
                                { name: 'White', color: '#FFFFFF' },
                                { name: 'Blue', color: '#8ED1FC' },
                            ] }
                            value={attributes.btnTextColor}
                            onChange={ ( newColor ) => setAttributes( { btnTextColor: newColor } ) }
                        />
						<h3 style={{marginTop:15}}>
                            {__("Background Color", "ht-contactform")}
                            <span
                                style={{
                                    ...colorSelectorStyles.selectedColorDisplay,
                                    backgroundColor:attributes.btnBackgroundColor
                                }}
                            ></span>
                        </h3>
                        <ColorPalette
                            colors={ [
                                { name: 'Black', color: '#000000' },
                                { name: 'Orange', color: '#FF6900' },
                                { name: 'Vivid Red', color: '#CF2E2E' },
                                { name: 'Pink', color: '#F78DA7' },
                                { name: 'White', color: '#FFFFFF' },
                                { name: 'Blue', color: '#8ED1FC' },
                            ] }
                            value={attributes.btnBackgroundColor}
                            onChange={ ( newColor ) => setAttributes( { btnBackgroundColor: newColor } ) }
                        />
						<DimensionsControl 
                            lavel = { __('Border Radius','ht-contactform') }
                            dimensions = { buttonBorderRadius }
                            attributesKey = { 'buttonBorderRadius' }
                            setAttributes = { setAttributes }
                        />
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Padding', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<DimensionsControl 
                            lavel = { __('Padding','ht-contactform') }
                            dimensions = { buttonPadding }
                            attributesKey = { 'buttonPadding' }
                            setAttributes = { setAttributes }
							responsive	  = 'yes'
							device		  = {this.state.device}
                        />
						<PanelRow className="ht-contactform-device-row" style={{ minHeight: 'auto' }}>
							<label>{ __( 'Device For Margin', 'ht-contactform' ) }</label>

							<ButtonGroup className="ht-contactform-device-button">
								<Button
									icon='desktop'
									value= 'desktop'
									label={ __( 'Large', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'desktop'  }
									isSecondary= { this.state.device !== 'desktop' }
									onClick={ (event) => handleTabs( event, 'device', 'desktop' ) }
								/>
								<Button
									icon='laptop'
									value= 'laptop'
									label={ __( 'Medium', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'laptop'  }
									isSecondary= { this.state.device !== 'laptop' }
									onClick={ (event) => handleTabs( event, 'device', 'laptop' ) }
								/>
								<Button
									icon='tablet'
									value= 'tablet'
									label={ __( 'Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'tablet'  }
									isSecondary= { this.state.device !== 'tablet' }
									onClick={ (event) => handleTabs( event, 'device', 'tablet' ) }
								/>
								<Button
									icon='smartphone'
									value= 'mobile'
									label={ __( 'Extra Small', 'ht-contactform' ) }
									isPrimary= {  this.state.device === 'mobile'  }
									isSecondary= { this.state.device !== 'mobile' }
									onClick={ (event) => handleTabs( event, 'device', 'mobile' ) }
								/>
							</ButtonGroup>
                        </PanelRow>
						<DimensionsControl 
                            lavel = { __('Margin','ht-contactform') }
                            dimensions = { buttonMargin }
                            attributesKey = { 'buttonMargin' }
                            setAttributes = { setAttributes }
							responsive	  = 'yes'
							device		  = {this.state.device}
                        />
		            </PanelBody> */}
		        </InspectorControls>
		        <style type="text/css">
					{formStyle}
				</style>
		    </Fragment>
    	);
    }
}
export default Inspector;
