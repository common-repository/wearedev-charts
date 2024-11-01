tinymce.PluginManager.add('wearedev_charts', function (editor) {
  var chartToolbar, serializer,
    DOM = tinymce.DOM,
    settings = editor.settings,
    Factory = tinymce.ui.Factory,
    each = tinymce.each,
    iOS = tinymce.Env.iOS,
    toolbarIsHidden = true,
    editorWrapParent = tinymce.$('#postdivrich');

  editor.addButton('wearedev_chart_remove', {
    tooltip: 'Remove chart',
    icon: 'dashicon dashicons-no',
    onclick: function () {
      alert('wearedev_chart_remove');
    }
  });

  editor.addButton('wearedev_chart_edit', {
    tooltip: 'Edit chart',
    icon: 'dashicon dashicons-chart-bar',
    onclick: function () {
      alert('wearedev_chart_edit');
    }
  });

  function toolbarConfig() {
    var toolbarItems = [],
      buttonGroup;

    each(['wearedev_chart_edit', 'wearedev_chart_remove'], function (item) {
      var itemName;

      function bindSelectorChanged() {
        var selection = editor.selection;

        if (item.settings.stateSelector) {
          selection.selectorChanged(item.settings.stateSelector, function (state) {
            item.active(state);
          }, true);
        }

        if (item.settings.disabledStateSelector) {
          selection.selectorChanged(item.settings.disabledStateSelector, function (state) {
            item.disabled(state);
          });
        }
      }

      if (item === '|') {
        buttonGroup = null;
      } else {
        if (Factory.has(item)) {
          item = {
            type: item
          };

          if (settings.toolbar_items_size) {
            item.size = settings.toolbar_items_size;
          }

          toolbarItems.push(item);

          buttonGroup = null;
        } else {
          if (!buttonGroup) {
            buttonGroup = {
              type: 'buttongroup',
              items: []
            };

            toolbarItems.push(buttonGroup);
          }

          if (editor.buttons[item]) {
            itemName = item;
            item = editor.buttons[itemName];

            if (typeof item === 'function') {
              item = item();
            }

            item.type = item.type || 'button';

            if (settings.toolbar_items_size) {
              item.size = settings.toolbar_items_size;
            }

            item = Factory.create(item);
            buttonGroup.items.push(item);

            if (editor.initialized) {
              bindSelectorChanged();
            } else {
              editor.on('init', bindSelectorChanged);
            }
          }
        }
      }
    });

    return {
      type: 'panel',
      layout: 'stack',
      classes: 'toolbar-grp inline-toolbar-grp wp-charts-toolbar',
      ariaRoot: true,
      ariaRemember: true,
      items: [
        {
          type: 'toolbar',
          layout: 'flow',
          items: toolbarItems
        }
      ]
    };
  }

  chartToolbar = Factory.create(toolbarConfig()).renderTo(document.body).hide();

  function parseChartShortcode (content) {
    var url = wpChartsPluginUrl;
    return content.replace(/\[wearedev_charts([^\]]*)\]/g, function (fullShortcode, attributes) {
      var type = attributes.split('type="').pop().split('"');
      type = type[0];
      return '<img class="wearedev-chart" style="background: #f7f7f7; border: 1px solid #cccccc; max-width: 200px; line-height: 200px; padding: 25px 0; border-radius: 3px;" src="'+ url +'/assets/images/wearedev-charts_'+ type +'.svg" title="wearedev_charts'+tinymce.DOM.encode(attributes)+'" />';
    });
  }

  function getChartShortcode (content) {
    function getChartAttr (placeholderImage, placeholderImageAttributes) {
      placeholderImageAttributes = new RegExp(placeholderImageAttributes + '=\"([^\"]+)\"', 'g').exec(placeholderImage);
      return placeholderImageAttributes ? tinymce.DOM.decode(placeholderImageAttributes[1]) : '';

    };

    return content.replace(/(?:<p[^>]*>)*(<img class="wearedev-chart"[^>]+>)(?:<\/p>)*/g, function (placeholderImageParagraph, placeholderImage) {

      var placeholderImageClass = getChartAttr(placeholderImage, 'class');

      if (placeholderImageClass.indexOf('wearedev-chart') != -1) {
        return '<p>[' + tinymce.trim(getChartAttr(placeholderImage, 'title')) + ']</p>';
      }

      return content;
    });
  }

  editor.wpSetChartCaption = function (content) {
    return parseChartShortcode(content);
  };

  editor.wpGetChartCaption = function (content) {
    return getChartShortcode(content);
  };

  editor.on('BeforeSetContent', function (event) {
    if (event.format !== 'raw') {
      event.content = editor.wpSetChartCaption(event.content);
    }
  });

  editor.on('PostProcess', function (event) {
    if (event.get) {
      event.content = editor.wpGetChartCaption(event.content);
    }
  });

  return {
    _do_shcode: parseChartShortcode,
    _get_shcode: getChartShortcode
  };
});
