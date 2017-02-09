/*global $j */

var ANN = {};

ANN.count = 0;
ANN.artefact  = null;
ANN.view      = null;
ANN.collection = [];

function cleanWhitespace(node) {
    var i, child;
    for (i = 0; i < node.childNodes.length; i++) {
        child = node.childNodes[i];
        if(child.nodeType == 3 && !/\S/.test(child.nodeValue)) {
            node.removeChild(child);
            i--;
        }
        if(child.nodeType == 1) {
            cleanWhitespace(child);
        }
    }
    return node;
}


function hideAnnotations() {
    $j('div.annotation').css('z-index', '10');
    $j('div.annotation').css('background-color', '#ffb');
    $j('div.annotation').children('a').text('x');
    $j('div.annotation').children('textarea').css('height', '15px');
    $j('div.annotation').children('textarea').css('overflow', 'hidden');
    $j('div.annotation').children('textarea').scrollTop(0);

    // show first 30 characters or first line
    $j('div.annotation').each(function () {
        var txt, m;

        txt = $j(this).children('textarea').get(0);
        m = txt.value.match(/^([^\n]+)\n/);
        if (m && m[1].length < 30) {
            txt.value = m[1].replace(/\s+$/, '') + '...';
        }
        else if (txt.value.length > 30) {
            txt.value = txt.value.substring(0, 27).replace(/\s+$/, '') + '...';
        }
    });
}

function prepareAnnotationHTML() {
//    document.body.style.height = $j(document).height() + 250 + 'px';

    $j("#column-container").css('width', '500px');
    $j("#column-container").css('padding', '10px');
    $j("#column-container").css('float', 'left');

    $j("div.viewfooter").hide();
}

function annotationAuthorHTML(author) {
    var cont, img, name;

    cont = document.createElement('span');

    img = document.createElement('img');
    img.src = author.image; img.alt = 'icon';


    name = document.createElement('a');
    name.href = author.profile;
    name.className = 'annotation-author';

    name.appendChild(img);

    name.appendChild(document.createTextNode(author.name));

    cont.appendChild(document.createTextNode(strings.annotatedby + ' '));
    cont.appendChild(name);
    return cont;
}

function showAnnotationHTML(author, data) {

    var div, btn, root, orig, cssApplier, i;

    function annotationBox(d) {
        var box, txt, ex, ii;

        box = $j('<div class="annotation">');
        box.css('top', parseInt(d.offset, 10) + 'px');

        txt =  $j('<textarea>');
        txt.val(d.txt);
        txt.attr('readonly', 'readonly');
        txt.css('cursor', 'pointer');
        box.append(txt);

        ex =  $j('<a href="#" class="ann-close">&ndash;</a>');

        ex.click(function () {
            hideAnnotations();
            $j('div.annotation').children('a').text('');
            root.innerHTML = orig;
            txt.blur();
            return false;
        });

        box.append(ex);

        box.click(function (e) {
            hideAnnotations();
            $j('div.annotation').children('a').text('');

            ex.html('&ndash;');
            root.innerHTML = orig;

            for (ii = 0; ii < d.hl.length; ++ii) {
                if (d.hl[ii].match(/^[a-f0-9,\{\}\/\:]+$/i)) {
                    // This will most likely fail if artefact has been modified after annotation.
                    try {
                        rangy.deserializeSelection(d.hl[ii], root);
                        cssApplier.applyToSelection();
                        rangy.getSelection().removeAllRanges();
                    } catch (ee) { }
                }
            }
            box.css('background-color', '#ffb');
            box.css('z-index', '11');
            txt.css('height', '150px');
            txt.val(d.txt);
            txt.css('overflow', 'auto');

            e.stopImmediatePropagation();
        });
        return box;
    }

    prepareAnnotationHTML();

    div = document.createElement('div');
    div.id = 'annotation-menu';

    div.appendChild(annotationAuthorHTML(author));

    btn = document.createElement('a');
    btn.href = '#'; btn.innerHTML = strings.back;
    btn.className = 'btn';
    btn.onclick = function () {
        window.location.reload(1);
        return false;
    };
    div.appendChild(btn);

    $j('body').append(div);

    div = document.createElement('div');
    div.id = "annotation-board";
    div.style.height = $j('#column-container').height() + 'px';

    $j('#view').append(div);

    root = document.getElementById('column-container');
    root.normalize();
    orig = cleanWhitespace(root).innerHTML;
    cssApplier = rangy.createCssClassApplier("annotate-yellow", {normalize: true});

    root.innerHTML = orig;

    for (i = 0; i < data.length; ++i) {
        $j('#annotation-board').append(annotationBox(data[i]));
    }

    $j('#annotation-board').click(function (e) {
        hideAnnotations();
        $j('div.annotation').children('a').text('');
        root.innerHTML = orig;
        e.stopImmediatePropagation();
    });

    $j('#annotation-board').click();

}

function newAnnotationHTML() {

    var div, h, btn;

    prepareAnnotationHTML();

    div = document.createElement('div');
    div.id = 'annotation-menu';

    h = document.createElement('span');
    h.className = 'header';
    h.innerHTML = strings.annotate;
    div.appendChild(h);


    btn = document.createElement('a');
    btn.href = '#'; btn.innerHTML = strings.cancel;
    btn.className = 'btn';
    btn.onclick = function () {
        window.location.reload(1);
        return false;
    };
    div.appendChild(btn);

    btn = document.createElement('a');
    btn.href = '#'; btn.innerHTML = strings.submit;
    btn.className = 'btn';
    btn.onclick = function () {
        var data = $j.grep(ANN.collection, function (elm) {return !!elm;});
        if (data.length) {
            sendjsonrequest(config.wwwroot + 'artefact/annotate/annotate.json.php', {
                    action: 'create',
                    artefact: ANN.artefact,
                    view:     ANN.view,
                    data: JSON.stringify(data)
                }, 'POST', function (res) {
                    window.location.reload(1);
                }
            );
        }
        return false;
    };
    div.appendChild(btn);

    $j('body').append(div);

    div = document.createElement('div');
    div.id = "annotation-board";
    div.style.height = $j('#column-container').height() + 'px';

    $j('#view').append(div);
}



function initAnnotate(showlink) {
    var m, a;
    m = window.location.search.match(/(?:\&|\?)view=([0-9]+)/);
    ANN.view = parseInt(m[1], 10);

    m = window.location.search.match(/(?:\&|\?)artefact=([0-9]+)/);
    ANN.artefact = parseInt(m[1], 10);

    if (!ANN.artefact || !ANN.view) {
        return;
    }
    rangy.init();

    $j('a.annotation-link').text(get_string('annotationlinktext'));
    $j('a.annotation-link').click(function () {
        sendjsonrequest(config.wwwroot + 'artefact/annotate/annotate.json.php', {
                action: 'show',
                artefact: ANN.artefact,
                view:     ANN.view,
                annotation: this.hash.replace(/\D+/,'')
            }, 'GET',
            function (data) {
                if (!data.annotation) {
                    alert("Failed to load annotation.");
                    return;
                }
                showAnnotationHTML(data.author, JSON.parse(data.annotation));
                $j(window).scrollTop(0);
            }
        );
        return false;
    });

    if (showlink) {
        a = $j('<a href="#" id="add-annotation">' + strings.annotate + '</a>');
        $j('#add_feedback_link').after(a);

        a.bind('click', function () {
            var clicked, open, root, orig;

            newAnnotationHTML();

            rangy.init();
            cssApplier = rangy.createCssClassApplier("annotate-yellow", {normalize: false});

            clicked = null;
            open = null;

            root = document.getElementById('column-container');
            root.normalize();
            orig = cleanWhitespace(root).innerHTML;

            root.innerHTML = orig;

            $j('#annotation-board').click(function (e) {
                hideAnnotations();
                open = null;
                root.innerHTML = orig;
                e.stopImmediatePropagation();
            });

            var top_offset = $j('.sitemessages').height() || 0;

            $j('#column-container').bind('mousedown', function (e) {
                clicked = {y: e.pageY - top_offset, x: e.pageX};
            });

            $j(document).bind('mouseup', function (e) {
                var hl, offset, i, prev, box, txt, ex;
                //alert(clicked.x + ',' + clicked.y);
                if (clicked && (e.pageX !== clicked.x || e.pageY !== clicked.y)) {

                    hl = rangy.serializeSelection(rangy.getSelection(), true, root);

                    if (open === null) {
                        root.innerHTML = orig;
                        open = ANN.count++;

                        offset = Math.min(e.pageY, clicked.y) - $j('#top-wrapper').height() - top_offset - 50;

                        dirty = true;
                        while (dirty) {
                            dirty = false;
                            for (i = 0; i < ANN.collection.length; ++i) {
                                if (ANN.collection[i]) {
                                    prev = ANN.collection[i].offset;
                                    if (offset >= prev ) {
                                        if (prev + 40 > offset) {
                                            offset += 45;
                                            dirty = true;
                                            break;
                                        }
                                    }
                                    else {
                                        if (offset + 40 > prev) {
                                            offset += 45;
                                            dirty = true;
                                            break;
                                        }
                                    }
                                }
                            }
                        }
                        $j('#annotation-board').css('min-height', offset + 30 + 'px');

                        ANN.collection[open] = { hl: [hl], offset: offset };

                        box = $j('<div class="annotation" id="abox' + open + '">');
                        box.css('top', offset + 'px');
                        txt =  $j('<textarea>');
                        box.append(txt);
                        ex =  $j('<a href="#" class="ann-close">&ndash;</a>');

                        ex.click(function () {
                            if (open !== null) {
                                hideAnnotations();
                                root.innerHTML = orig;
                                open = null;
                            }
                            else if (confirm(strings.confirmdelete)) {
                                delete ANN.collection[box.attr('id').replace(/\D+/, '')];
                                box.remove();
                            }
                            txt.blur();
                            return false;
                        });

                        box.append(ex);
                        $j('#annotation-board').append(box);

                        txt.change(function () {
                            ANN.collection[open].txt = txt.val();
                        });

                        box.click(function (e) {
                            var id, hl_list, i;

                            e.stopImmediatePropagation();
                            idx = box.attr('id').replace(/\D+/, '');
                            if (open === idx) {
                                return;
                            }

                            hideAnnotations();

                            ex.html('&ndash;');
                            root.innerHTML = orig;

                            hl_list = ANN.collection[idx].hl;
                            for (i = 0; i < hl_list.length; ++i) {
                                try {
                                    rangy.deserializeSelection(hl_list[i], root);
                                    cssApplier.applyToSelection();
                                    rangy.getSelection().removeAllRanges();
                                } catch (ee) { }
                            }

                            box.css('background-color', '#ffb');
                            box.css('z-index', '11');
                            txt.css('height', '150px');
                            txt.css('overflow', 'auto');
                            open = idx;

                            txt.val(ANN.collection[open].txt);
                            txt.focus();
                        });

                        try {
                            rangy.deserializeSelection(hl, root);
                            cssApplier.applyToSelection();
                            rangy.getSelection().removeAllRanges();
                        } catch (ee) { }

                        txt.focus();
                    }
                    else {
                        ANN.collection[open].hl.push(hl);
                        cssApplier.applyToSelection();
                        rangy.getSelection().removeAllRanges();
                    }
                }
                clicked = null;
            });
            return false;
        });
    }
}

