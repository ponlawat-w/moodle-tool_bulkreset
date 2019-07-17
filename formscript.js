require(['jquery'], $ => {
    $(document).ready(() => {
        const $selectallcheckboxes = $('.tool-bulkreset-selectall');
        const $deselectallcheckboxes = $('.tool-bulkreset-deselectall');
        const $selectallall = $('#tool-bulkreset-selectallall');
        const $deselectallall = $('#tool-bulkreset-deselectallall');

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
        })
    });
});
