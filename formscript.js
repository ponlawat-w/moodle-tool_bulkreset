require(['jquery'], $ => {
    $(document).ready(() => {
        const $selectallcheckboxes = $('.tool-bulkreset-selectall');
        const $deselectallcheckboxes = $('.tool-bulkreset-deselectall');
        const $selectallall = $('#tool-bulkreset-selectallall');
        const $deselectallall = $('#tool-bulkreset-deselectallall');
        const $sortselect = $('#tool-bulk_reset-sort_select');

        const setall = (event, value) => {
            $(event.target).parent().parent().find('input[type="checkbox"]').prop('checked', value);
        };

        const clickall = array => {
            $.each(array, (index, element) => {
                element.click();
            });
        };

        $selectallcheckboxes.click(e => {
            setall(e, true);
        });

        $deselectallcheckboxes.click(e => {
            setall(e, false);
        });

        $selectallall.click(() => {
            clickall($selectallcheckboxes);
        });

        $deselectallall.click(() => {
            clickall($deselectallcheckboxes);
        });

        const locations = location.href.split('?');
        let params = {};
        if (locations[1]) {
            params = locations[1].split('&').reduce((obj, curr) => {
                const paramsegments = curr.split('=');
                if (paramsegments.length < 2) {
                    return obj;
                }
                obj[paramsegments[0]] = decodeURIComponent(paramsegments[1]);
                return obj;
            }, {});
            if (params.sort) {
                $sortselect.val(params.sort);
            }
        }
        $sortselect.change(() => {
            params.sort = $sortselect.val();
            window.location = location.href.split('?')[0] + `?${Object.keys(params).map(k => `${k}=${encodeURIComponent(params[k])}`)}`;
        });
    });
});
