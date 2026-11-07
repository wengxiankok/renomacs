// Dependency-free behavioural checks of the actual inline script with DOM/network doubles.
// No browser, HTTP requests, or WordPress database mutations.
const {readFileSync} = require('node:fs');
const {resolve} = require('node:path');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = readFileSync(resolve(__dirname, '../../../wp-content/themes/renomacs/contact-form.php'), 'utf8')
  .match(/<script>([\s\S]*?)<\/script>/)[1]
  .replace(/<\?php[\s\S]*?\?>/, '"http://localhost:8080/wp-json/custom/v1/contact"');
function setup(fetchImpl, valid = true) {
  const nodes = {};
  let handler, timer, calls = 0, focused;
  for (const key of ['customForm', 'button', 'enquiry-error', 'enquiry-error-text', 'form-spinner', '.content-wrapper', '.thank-you-message']) {
    const classes = new Set(['enquiry-error', 'form-spinner', '.thank-you-message'].includes(key) ? ['d-none'] : []);
    nodes[key] = {value: 'retained', disabled: false, textContent: '', attributes: {},
      classList: {add: c => classes.add(c), remove: c => classes.delete(c), contains: c => classes.has(c)},
      focus: () => { focused = key; }, setAttribute(k,v) {this.attributes[k]=v;}, removeAttribute(k) {delete this.attributes[k];}};
  }
  const form = nodes.customForm;
  form.querySelector = () => nodes.button;
  form.reportValidity = () => valid;
  form.addEventListener = (_, fn) => {handler = fn;};
  form.reset = () => {throw Error('Must not discard enquiry');};
  vm.runInNewContext(source, {
    document: {getElementById: id => nodes[id], querySelector: key => nodes[key]},
    FormData: class {constructor(f) {assert.equal(f, form);}}, AbortController,
    setTimeout: (fn, ms) => {assert.equal(ms,45000); timer = fn; return 1;}, clearTimeout: () => {},
    fetch: (...args) => {calls++; return fetchImpl(...args);}
  });
  return {nodes, submit: () => handler({preventDefault() {}}), timeout: () => timer(), calls: () => calls, focus: () => focused};
}
const response = (status, body) => ({ok: status >= 200 && status < 300, status, json: async () => body});
let passed = 0;
async function test(name, fn) {await fn(); passed++; console.log('PASS: ' + name);}
(async () => {
  await test('Invalid browser form never sends', async () => {const t=setup(()=>{}, false); await t.submit(); assert.equal(t.calls(),0);});
  await test('Pending duplicate blocked; success focused; pending controls restored', async () => {
    let finish; const t=setup(()=>new Promise(resolve => {finish=resolve;}));
    const pending=t.submit(); assert.equal(t.nodes.button.disabled,true); assert.equal(t.nodes.customForm.attributes['aria-busy'],'true');
    await t.submit(); assert.equal(t.calls(),1); finish(response(200,{result:'success'})); await pending;
    assert.equal(t.focus(),'.thank-you-message'); assert.equal(t.nodes.customForm.classList.contains('d-none'),true);
    assert.equal(t.nodes.button.disabled,false); assert.equal(t.nodes['form-spinner'].classList.contains('d-none'),true);
  });
  for (const [name, impl, message] of [
    ['HTTP 503', async()=>response(503,{result:'error',message:'Temporarily unavailable'}),'Temporarily unavailable'],
    ['HTTP 400', async()=>response(400,{result:'error',message:'Check phone'}),'Check phone'],
    ['HTTP 429', async()=>response(429,{result:'error',message:'Wait before retry'}),'Wait before retry'],
    ['HTTP 500 with misleading success', async()=>response(500,{result:'success'})],
    ['HTTP 200 with error', async()=>response(200,{result:'error'})],
    ['Malformed JSON', async()=>({ok:true,status:200,json:async()=>{throw Error('JSON');}})],
    ['Null JSON', async()=>response(200,null)],
    ['Offline', async()=>{throw Error('offline');}]
  ]) { await test(name + ' preserves form and restores controls',async()=>{
    const t=setup(impl); await t.submit(); assert.equal(t.focus(),'enquiry-error');
    assert.equal(t.nodes.customForm.value,'retained'); assert.equal(t.nodes.customForm.classList.contains('d-none'),false);
    assert.equal(t.nodes.button.disabled,false); assert.equal(t.nodes['form-spinner'].classList.contains('d-none'),true);
    assert.equal(t.nodes.customForm.attributes['aria-busy'],undefined);
    if(message) assert.equal(t.nodes['enquiry-error-text'].textContent,message);
  }); }
  await test('Timeout aborts once and preserves enquiry without automatic retry',async()=>{
    const t=setup((url,options)=>new Promise((resolve,reject)=>options.signal.addEventListener('abort',()=>reject(Error('aborted')))));
    const pending=t.submit(); t.timeout(); await pending; assert.equal(t.calls(),1); assert.equal(t.focus(),'enquiry-error'); assert.equal(t.nodes.button.disabled,false);
  });
  console.log(`${passed} frontend behavioural scenarios passed (DOM/network doubles, not a browser test).`);
})().catch(error=>{console.error(error);process.exitCode=1;});
