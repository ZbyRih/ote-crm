import Mask from 'inputmask';

export default (e, strMask, opts) => {
	let m = new Mask(strMask, opts);
	m.mask(e);
}