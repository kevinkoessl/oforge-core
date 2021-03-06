// CMS Pages Controller Module
// will be initialized on load of pages/index.twig
var cmsPagesControllerModule = (function() {
	// bind functions to window resize event
	$(window).resize(function() {
		cpcm.resizePageBuilder();
	});

	// jsTree callback functions
	$('#cms_pages_controller_jstree').on('loaded.jstree', function (event, data) {
		$('#cms_pages_controller_jstree').jstree('open_all');
	});

	$('#cms_pages_controller_jstree').on('select_node.jstree', function (event, data) {
		// switch page on page select
		$('#cms_page_jstree_selected_page').val($('#cms_pages_controller_jstree').jstree('get_selected'));
		$('#cms_page_selected_element').val('');
		$('#cms_page_builder_form').submit();
	});

	// called after creating the node in jstree. afterwards rename_node.jstree-callback
	// will be called when user finished editing the node's name
	$('#cms_pages_controller_jstree').on('create_node.jstree', function (event, data) {
		var node = data.node;
		var parent = data.parent;
		var position = data.position;
		
		$('#cms_edit_page_parent_id').val(node.parent);
		$('#cms_edit_page_action').val('create');
	});

	// called after user finished editing the node's name
	$('#cms_pages_controller_jstree').on('rename_node.jstree', function (event, data) {
		var node = data.node;
		var text = data.text;
		var old = data.old;

		if ($('#cms_edit_page_action').val() != 'create') {
			$('#cms_edit_page_id').val(node.id);
			$('#cms_edit_page_action').val('rename');
		}

		$('#cms_edit_page_name').val(node.text);
		$('#cms_page_jstree_form').submit();
	});

	// called after user finished editing the node's name
	$('#cms_pages_controller_jstree').on('duplicate_node.jstree', function (event, node) {
        $('#cms_edit_page_id').val(node.id);
        $('#cms_edit_page_parent_id').val(node.parent);
        $('#cms_edit_page_action').val('duplicate');
        $('#cms_page_jstree_form').submit();
	});

    // called after deleting a jstree node
    $('#cms_pages_controller_jstree').on('delete_node.jstree', function (event, data) {
        var node = data.node;
        var parent = data.parent;

        $('#cms_edit_page_id').val(node.id);
        $('#cms_edit_page_action').val('delete');
        $('#cms_page_jstree_form').submit();
    });

	// on click create new root page
	$('#cms-page-builder-create-new-root-page').click(
		function() {
			var tree = $('#cms_pages_controller_jstree').jstree(true);
			var node = tree.get_node("#");
			
			node = tree.create_node(node, {"type":"folder"});
			tree.edit(node);
		}
	);

	// mark and select selectable elements in page builder
	$('[data-pb-id]').each(
		function() {
			var selectedElement = '^(' + $(this).attr('data-pb-se') + '\-)';
			var regularExpression = new RegExp(selectedElement);
			
			if (
				$(this).attr('data-pb-id') != $(this).attr('data-pb-se')
				&& $(this).attr('data-pb-id').startsWith($(this).attr('data-pb-se'))
				&& $(this).attr('data-pb-id').replace(regularExpression, '').indexOf('-') === -1
			) {
				// add delete button to element
				$(this).append('<div class="content-type-delete-button" onclick="deleteContentType(event, this)"><img src="/Themes/Base/Backend/__assets/img/pagebuilder/delete.svg"></div>');

				// mark selectable elements in page builder on mouse hover
				$(this).hover(
					function() {
						$(this).addClass("cms-page-builder-selected-element");
					},
					function() {
						$(this).removeClass("cms-page-builder-selected-element");
					}		
				);
						
				// select element in page builder on mouse click
				$(this).click(
					function() {
						var target = $(this).data("click-false");
						if(target){
							return;
						}

						$('#cms_page_selected_element').val($(this).attr('data-pb-id'));
						$('#cms_page_builder_form').submit();
					}
				);
			}
		}
	);

    $('#cms_page_builder_language_selector').change(
    	function() {
    		$('#cms_page_selected_language').val($('#cms_page_builder_language_selector option:selected').val());

    		$('#cms_page_builder_form').submit();
    	}
	);
	
	if (typeof cms_pages_controller_jstree_config !== typeof undefined && cms_pages_controller_jstree_config) {
		// create jstree configs
		var jsTreeConfig = {
            "core" : {
                "multiple"       : false,
                "animation"      : 0,
                "check_callback" : true,
                "force_text"     : true,
                "themes"         : {"stripes" : false},
                "data"           : cms_pages_controller_jstree_config
			},
			"plugins" : ["contextmenu"],
			"contextmenu" : {
				"select_node" : false,
				"show_at_node" : true,
				"items" : {
					"createItem": {
						"label": "Create",
						"action": function (obj) {
							var tree = $('#cms_pages_controller_jstree').jstree(true);
							var node = tree.get_node(obj.reference);
							
							node = tree.create_node(node, {"type":"folder"});
							tree.edit(node);
						}
					},
					"renameItem": {
						"label": "Rename",
						"action": function (obj) {
							var tree = $('#cms_pages_controller_jstree').jstree(true);
							var node = tree.get_node(obj.reference);
							
							tree.edit(node);
						}
					},
					"duplicateItem": {
						"label": "Duplicate",
						"action": function (obj) {
							var tree = $('#cms_pages_controller_jstree').jstree(true);
                            tree.trigger('duplicate_node', tree.get_node(obj.reference));
						}
					},
					"deleteItem": {
						"label": "Delete",
						"action": function (obj) {
							var tree = $('#cms_pages_controller_jstree').jstree(true);
							var node = tree.get_node(obj.reference);
							
							tree.delete_node(node);
						}
					}
				}
			}
		};

		// create jstree object
		$('#cms_pages_controller_jstree').jstree(jsTreeConfig);
	}

	console.log("-= CMS Pages Controller Module has been initialized! =-");

	return {
		// adopt cms content builder containers to parents height on window resize event
		resizePageBuilder : function () {
			if (typeof $('#page_builder_container_wrapper') !== typeof undefined && typeof $('#page_builder_container_wrapper').position() !== typeof undefined) {
				var calculatedHeight = window.innerHeight - $('#page_builder_container_wrapper').position().top - $('.main-footer').outerHeight(true) - $('.content').css('padding-top').replace('px', '') - $('.content').css('padding-bottom').replace('px', '');
			
				$('#page_builder_container_wrapper').height(calculatedHeight);
				
				$('#cms_page_jstree_container').height(calculatedHeight);
				$('#cms_page_builder_container').height(calculatedHeight);
				$('#cms_content_type_list_container').height(calculatedHeight);
				
				if (typeof $('#cms_content_type_editor_wrapper') !== typeof undefined && typeof $('#cms_content_type_editor_wrapper').position() !== typeof undefined) {
					$('#cms_content_type_editor_wrapper').height(calculatedHeight - $('#cms_content_type_editor_wrapper').position().top);
				}
			}
		}
	}
});

// check if the Oforge namespace exists
if (typeof Oforge !== 'undefined') {
    // if it exists, it should have the register function, so register your module
    // the properties "name", "selector" and "init" are required
    // name: the name of your module
    // selector: the html selector to search for. If it is found, the module can be initialized
    // init: the function to initialize the module. This function gets called automatically from the module-loader.js
    // when the DOMContentLoaded event is triggered.
    Oforge.register({
        name: 'cmsPagesControllerModule',
        selector: '#page_builder_container_wrapper',
        init: function () {
			window.cpcm = cmsPagesControllerModule();

			cpcm.resizePageBuilder();
        }
    });
} else {
    console.warn("Oforge is not defined. Module cannot be registered.");
}
