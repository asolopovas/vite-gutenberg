import {registerBlockType} from '@wordpress/blocks'


registerBlockType( 'test/test', {
    edit: function () {
        return <p> Hello world (from the editor)</p>;
    },
    save: function () {
        return <p> Hola mundo (from the frontend) </p>;
    },
} );
