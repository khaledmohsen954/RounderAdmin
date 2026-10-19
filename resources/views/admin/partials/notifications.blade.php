<div class="dropdown" id="admin-notifications" data-feed="{{ route('admin.notifications.index') }}" data-read-all="{{ route('admin.notifications.read-all') }}" data-read-template="{{ route('admin.notifications.read',['id'=>0]) }}">
<button class="btn btn-sm btn-outline-secondary position-relative" id="notification-bell" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="{{ __('Notifications') }}">
<i class="fa-solid fa-bell" aria-hidden="true"></i><span id="notification-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" aria-live="polite"></span>
</button>
<div class="dropdown-menu dropdown-menu-end p-0 shadow notification-dropdown" aria-labelledby="notification-bell">
<div class="d-flex align-items-center justify-content-between p-3 border-bottom gap-2">
<strong>{{ __('Notifications') }}</strong><button class="btn btn-link btn-sm p-0" type="button" id="notifications-read-all">{{ __('Mark all as read') }}</button>
</div>
<div id="notification-list" class="p-2" role="status">{{ __('Loading notifications') }}</div>
<div class="small text-secondary p-2 border-top">{{ __('Notifications refresh every 30 seconds') }}</div>
</div>
</div>
@push('scripts')
<script>
(() => {
 const root=document.getElementById('admin-notifications');
 const list=document.getElementById('notification-list');
 const badge=document.getElementById('notification-count');
 const all=document.getElementById('notifications-read-all');
 const messages={empty:@json(__('No notifications')),failed:@json(__('Could not load notifications')),read:@json(__('Mark as read')),open:@json(__('Open section')),urgent:@json(__('Urgent')),reference:@json(__('Record number'))};
 let busy=false;
 async function mark(url) {
   const response=await fetch(url,{method:'POST',headers:{'Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())}});
   if(!response.ok) throw new Error('Notification update failed');
 }
 async function refresh() {
   if(busy || document.hidden) return;
   busy=true;
   try {
     const response=await fetch(root.dataset.feed,{headers:{'Accept':'application/json'},cache:'no-store'});
     if(!response.ok) throw new Error('Notification fetch failed');
     const data=await response.json();
     badge.textContent=data.unread>99?'99+':String(data.unread);
     badge.classList.toggle('d-none',data.unread===0);
     all.disabled=data.unread===0;
     list.replaceChildren();
     if(!data.items.length) { list.textContent=messages.empty; return; }
     for(const item of data.items) {
       const row=document.createElement('div');
       row.className='p-2 mb-1 rounded border-bottom'+(item.read?'':' bg-light');
       const title=document.createElement('strong');
       title.className=item.urgent?'d-block text-danger':'d-block';
       title.textContent=(item.urgent?messages.urgent+' · ':'')+item.title;
       const meta=document.createElement('small');
       meta.className='d-block text-secondary';
       meta.textContent=messages.reference+' #'+item.subject_id+' · '+new Date(item.time).toLocaleString(document.documentElement.lang);
       const link=document.createElement('a');
       link.className='btn btn-link btn-sm ps-0'; link.textContent=messages.open; link.href=item.url;
       const url=root.dataset.readTemplate.replace('/0/read','/'+item.id+'/read');
       link.addEventListener('click',async event=>{
         if(event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
         event.preventDefault();
         try { await mark(url); location.assign(item.url); } catch { link.textContent=messages.failed; }
       });
       row.append(title,meta,link);
       if(!item.read) {
         const read=document.createElement('button');
         read.type='button';read.className='btn btn-sm btn-outline-secondary';read.textContent=messages.read;
         read.addEventListener('click',async()=>{
           read.disabled=true;
           try { await mark(url); await refresh(); } catch { read.textContent=messages.failed;read.disabled=false; }
         });
         row.append(read);
       }
       list.append(row);
     }
   } catch { list.textContent=messages.failed; } finally {busy=false;}
 }
 all.addEventListener('click',async()=>{
   all.disabled=true;
   try { await mark(root.dataset.readAll); await refresh(); } catch {list.textContent=messages.failed;all.disabled=false;}
 });
 root.addEventListener('show.bs.dropdown',refresh);
 document.addEventListener('visibilitychange',()=>{if(!document.hidden)refresh();});
 refresh(); setInterval(refresh,30000);
})();
</script>
@endpush
