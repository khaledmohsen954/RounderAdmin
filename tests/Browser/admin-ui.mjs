import assert from 'node:assert/strict';
import {mkdir} from 'node:fs/promises';
const {chromium} = await import(process.env.PLAYWRIGHT_MODULE);
const browser = await chromium.launch({headless:true});
const page = await browser.newPage({viewport:{width:1440,height:1000}});
const errors=[];
page.on('pageerror', error=>errors.push(error.message));
const base='http://127.0.0.1:8000';
try {
  await page.goto(base+'/admin/login');
  await page.locator('input[name=email]').fill('admin-ui@example.test');
  await page.locator('input[name=password]').fill('StrongBrowserPassword123!');
  await Promise.all([page.waitForURL('**/admin/dashboard'),page.locator('form[action$="/admin/login"] button[type=submit], form[action$="/admin/login"] button').first().click()]);
  await page.locator('.kpi-card').first().waitFor();
  await page.locator('#notification-bell').click();
  await page.locator('.notification-dropdown').waitFor({state:'visible'});
  await page.waitForFunction(()=>!document.querySelector('#notification-list').textContent.includes('جارٍ'));
  assert.equal(await page.locator('#notification-list').textContent().then(t=>t.includes('تعذر')),false);
  await page.locator('#notification-bell').click();
  assert.ok(await page.locator('.kpi-card').count()>=10);
  await mkdir('tests/browser-artifacts',{recursive:true});
  await page.screenshot({path:'tests/browser-artifacts/dashboard-desktop.png',fullPage:true});
  for(const path of ['/admin/providers','/admin/providers/create','/admin/users/create','/admin/realestate/add',
      '/admin/services','/admin/services/create','/admin/packages','/admin/packages/create',
      '/admin/package-features','/admin/package-features/create','/admin/subscriptions',
      '/admin/permissions/create','/admin/emergencies/create']) {
    const response=await page.goto(base+path);
    assert.equal(response.status(),200,path);
  }
  await page.goto(base+'/admin/permissions/create');
  await page.locator('.row-toggle').first().check();
  assert.equal(await page.locator('tbody tr').first().locator('.permission-checkbox:checked').count(),4);
  await page.locator('.row-toggle').first().uncheck();
  assert.equal(await page.locator('tbody tr').first().locator('.permission-checkbox:checked').count(),0);
  await page.goto(base+'/admin/providers/create');
  await page.locator('#terms-editor').fill('Contract terms');
  await page.locator('#terms-editor').press('ControlOrMeta+A');
  await page.locator('[data-command=bold]').click();
  assert.match(await page.locator('#terms').inputValue(),/<(b|strong)>/);
  await page.goto(base+'/admin/services');
  const firstDelete=page.locator('.delete-record-form button').first();
  await firstDelete.click();
  await page.locator('#delete-confirmation').waitFor({state:'visible'});
  await page.locator('#delete-confirmation [data-bs-dismiss=modal]').click();
  await page.locator('#delete-confirmation').waitFor({state:'hidden'});
  await page.setViewportSize({width:390,height:844});
  for(const path of ['/admin/dashboard','/admin/realestate/add','/admin/emergencies/create','/admin/packages/create','/admin/permissions/create']) {
    await page.goto(base+path);
    const dimensions=await page.evaluate(()=>({width:innerWidth,scroll:document.documentElement.scrollWidth}));
    assert.ok(dimensions.scroll<=dimensions.width+1, path+' overflows viewport: '+JSON.stringify(dimensions));
  }
  await page.goto(base+'/admin/dashboard');
  await page.locator('[data-bs-target="#admin-menu"]').first().click();
  await page.locator('#admin-menu.show').waitFor();
  await page.locator('#admin-menu [data-bs-dismiss=offcanvas]').click();
  await page.locator('#admin-menu.show').waitFor({state:'hidden'});
  await page.screenshot({path:'tests/browser-artifacts/dashboard-mobile.png',fullPage:true});
  assert.deepEqual(errors,[],'Browser JavaScript errors');
} catch(error) {
  await mkdir('tests/browser-artifacts',{recursive:true});
  await page.screenshot({path:'tests/browser-artifacts/failure.png',fullPage:true});
  console.error('Failed browser URL:',page.url());
  throw error;
} finally {await browser.close();}
