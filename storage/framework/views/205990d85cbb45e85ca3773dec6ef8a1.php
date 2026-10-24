
<?php
    $formatKb = fn (int $kb) => $kb >= 1024 ? rtrim(rtrim(number_format($kb / 1024, 1), '0'), '.') . ' MB' : $kb . ' KB';
?>
<div data-media-picker data-limits='<?php echo json_encode($mediaLimits, 15, 512) ?>'>
    <input type="file" name="media[]" id="mediaInput" class="d-none" multiple accept="<?php echo e($mediaLimits['accept']); ?>">
    <label for="mediaInput" class="btn btn-outline-secondary btn-sm mb-0"><i class="ri-image-add-line"></i> Choose photos &amp; videos</label>
    <span class="small text-muted ms-2" data-media-summary>No files chosen</span>
    <div class="form-text">
        Select several at once, or add more in another go. Photos: JPG, PNG or WEBP up to <?php echo e($formatKb($mediaLimits['imageKb'])); ?>.
        Videos: MP4 or MOV up to <?php echo e($formatKb($mediaLimits['videoKb'])); ?>. Up to <?php echo e($mediaLimits['files']); ?> files / <?php echo e($formatKb($mediaLimits['totalKb'])); ?> per save.
    </div>
    <div class="row g-2 mt-1" data-media-list></div>
</div>

<script>
// Runs once the page is parsed: the form's submit button comes after this partial.
document.addEventListener('DOMContentLoaded', function () {
    const picker  = document.querySelector('[data-media-picker]');
    const input   = picker.querySelector('input[type=file]');
    const list    = picker.querySelector('[data-media-list]');
    const summary = picker.querySelector('[data-media-summary]');
    const submits = picker.closest('form').querySelectorAll('[data-media-submit]');
    const limits  = JSON.parse(picker.dataset.limits);
    const allowed = limits.accept.split(',');
    let files = [];
    let previewUrls = [];

    const fmt = (bytes) => bytes >= 1048576
        ? (bytes / 1048576).toFixed(1).replace(/\.0$/, '') + ' MB'
        : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    const escapeHtml = (text) => { const div = document.createElement('div'); div.textContent = text; return div.innerHTML; };

    function problemWith(file) {
        if (!allowed.includes(file.type)) return 'Not a JPG, PNG, WEBP, MP4 or MOV file';
        const isVideo = file.type.startsWith('video/');
        const maxKb = isVideo ? limits.videoKb : limits.imageKb;
        return file.size > maxKb * 1024 ? 'Too large (max ' + fmt(maxKb * 1024) + ')' : null;
    }

    function render() {
        // keep the real input's FileList in step with what's shown
        const transfer = new DataTransfer();
        files.forEach(file => transfer.items.add(file));
        input.files = transfer.files;

        previewUrls.forEach(url => URL.revokeObjectURL(url));
        previewUrls = [];

        let problems = 0;
        list.innerHTML = files.map((file, index) => {
            const problem = problemWith(file);
            if (problem) problems++;
            const url = URL.createObjectURL(file);
            previewUrls.push(url);
            const preview = file.type.startsWith('video/')
                ? `<video src="${url}" class="rounded w-100" style="height:90px;object-fit:cover;" muted preload="metadata"></video>`
                : `<img src="${url}" class="rounded w-100" style="height:90px;object-fit:cover;" alt="">`;
            return `
                <div class="col-6 col-md-3 col-lg-2">
                    <div class="position-relative border rounded p-1 ${problem ? 'border-danger' : ''}">
                        ${preview}
                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1" style="padding:.05rem .35rem;" data-remove="${index}" title="Remove"><i class="ri-close-line"></i></button>
                        <div class="small text-truncate mt-1" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                        <div class="small ${problem ? 'text-danger' : 'text-muted'}">${problem || fmt(file.size)}</div>
                    </div>
                </div>`;
        }).join('');

        const total = files.reduce((sum, file) => sum + file.size, 0);
        let message = files.length ? `${files.length} file${files.length === 1 ? '' : 's'} chosen (${fmt(total)})` : 'No files chosen';
        let blocked = problems > 0;

        if (files.length > limits.files) {
            message += ` — too many, up to ${limits.files} at a time`;
            blocked = true;
        } else if (total > limits.totalKb * 1024) {
            message += ` — more than ${fmt(limits.totalKb * 1024)} in one save, please save in smaller batches`;
            blocked = true;
        } else if (problems) {
            message += ` — remove the ${problems === 1 ? 'file' : 'files'} marked in red`;
        }

        summary.textContent = message;
        summary.classList.toggle('text-danger', blocked);
        summary.classList.toggle('text-muted', !blocked);
        submits.forEach(button => button.disabled = blocked || (button.dataset.mediaSubmit === 'required' && !files.length));
    }

    input.addEventListener('change', () => {
        // the browser replaces the selection on every pick, so add it to what was already chosen
        const picked = [...input.files].filter(file => !files.some(f => f.name === file.name && f.size === file.size && f.lastModified === file.lastModified));
        files = files.concat(picked);
        render();
    });

    list.addEventListener('click', (e) => {
        const button = e.target.closest('[data-remove]');
        if (!button) return;
        files.splice(Number(button.dataset.remove), 1);
        render();
    });

    // Uploaded in place (data-ajax, layouts/admin): start again with an empty selection
    picker.closest('form').addEventListener('admin:saved', () => {
        files = [];
        render();
    });

    render();
});
</script>
<?php /**PATH C:\Users\zubai\Herd\Ravellen\resources\views/admin/products/partials/media-picker.blade.php ENDPATH**/ ?>