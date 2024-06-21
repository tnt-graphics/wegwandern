window.addEventListener('load', () => {
  if (['post.php', 'post-new.php'].indexOf(my_editor_script.hook) > -1) {
    if (wp.data !== undefined) {
      wp.data.dispatch('core/edit-post').removeEditorPanel('taxonomy-panel-wander-saison');
    }
  }
})

if (wp.domReady !== undefined) {
  wp.domReady(() => {
    if (wp.blocks !== undefined) {
      wp.blocks.unregisterBlockStyle('core/button', 'outline');
      wp.blocks.unregisterBlockStyle('core/button', 'fill');

      wp.blocks.registerBlockStyle('core/button', {
        name: 'primary-button',
        label: 'Primary',
        isDefault: true
      });

      wp.blocks.registerBlockStyle('core/button', {
        name: 'download-button',
        label: 'Download'
      });

      wp.blocks.registerBlockStyle('core/button', {
        name: 'external-button',
        label: 'External'
      });
    }
  });
}

if (wp.blocks !== undefined) {
  wp.blocks.registerBlockVariation(
    'core/file',
    {
      isDefault: true,
      attributes: {
        displayPreview: false
      },
    }
  );
}