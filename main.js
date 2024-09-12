(function init() {
    const button = document.querySelector('.get-cards-button');
    const output = document.querySelector('.cards');

    const tmpl = document.querySelector('#card-template');
    const innerTmpl = document.querySelector('#card-inner-template');

    const itemsLimit = 3;
    const requestDelay = 1000;

    if (!button || !output || !tmpl || !innerTmpl) {
        console.error('Отсутствуют необходимые элементы в DOM-дереве');
        return;
    }

    button.addEventListener('click', handleButtonClick, { once: true });

    async function handleButtonClick() {
        button.hidden = true;

        initLoader(output, 'data-loader-outer');
        await renderCards({ target: 'leads' });

        output.addEventListener('click', handleCardClick);
    }

    async function handleCardClick(e) {
        const target = e.target.closest('.card[data-id]');
        const leadId = target?.dataset.id;

        if (!target || e.target.closest('.card__inner')) {
            return;
        }

        for (const card of output.children) {
            card.classList.remove('opened');
            card.querySelector('.card__inner')?.remove();
        }

        target.classList.add('opened');

        initLoader(target, 'data-loader-inner');
        await renderInnerCard(target, leadId);
        destroyLoader('data-loader-inner');

        const innerCard = target.querySelector('.card__inner');
        const closeButton = target.querySelector('.card__inner-close');

        closeButton.addEventListener('click', () => {
            innerCard.remove();
            target.classList.remove('opened');
        });
    }

    async function getData(options = {}) {
        const requests = {
            leads: `/api/v4/leads?limit=${itemsLimit}&page=${options.page || 1}`,
            lead: `/api/v4/leads/${options.id || 0}`,
        };

        const fetchOptions = {
            method: 'POST',
            headers: {
                'Content-Type': 'text/plain; charset=UTF-8',
            },
            body: requests[options.target],
        }

        try {
            const res = await fetch('api.php', fetchOptions);
            const data = await res.json();

            return data;
        }
        catch (error) {
            return { fetchError: error }
        }
    }

    async function renderCards(options) {
        const data = await getData(options);

        if (data.fetchError) {
            output.textContent = `
                Ошибка: ${data.fetchError.message}.
                Проверьте соединение или попробуйте позже
            `;
            return;
        }

        if (!data._embedded.leads.length) {
            output.textContent = 'Нет карточек для отображения';
            return;
        }

        data._embedded.leads.forEach(lead => {
            output.append(getCardElement(lead));
        });

        if (data._links.next) {
            const url = new URL(data._links.next.href);
            const nextPage = url.searchParams.get('page');

            setTimeout(() => {
                renderCards({ target: 'leads', page: nextPage });
            }, requestDelay);
        }
        else {
            destroyLoader('data-loader-outer');
        }
    }
    async function renderInnerCard(targetElem, id) {
        const cardDetails = await getData({ id, target: 'lead' });

        targetElem.append(getInnerCardElement(cardDetails));
    }

    function getCardElement({ id, name, price } = {}) {
        const cloneTmpl = tmpl.content.cloneNode(true);

        cloneTmpl.querySelector('.card').dataset.id = id;
        cloneTmpl.querySelector('.card__title').textContent = name;
        cloneTmpl.querySelector('.card__title').setAttribute('title', name);
        cloneTmpl.querySelector('.card__id').textContent = '#' + id;
        cloneTmpl.querySelector('.card__budget').textContent = price + ' р.';

        return cloneTmpl;
    }

    function getTaskStatus(taskTimeStamp) {
        const taskDeadline = new Date(taskTimeStamp).setHours(0, 0, 0, 0);
        const currentDay = new Date().setHours(0, 0, 0, 0);

        return taskDeadline - currentDay === 0
            ? 'green'
            : taskDeadline - currentDay < 0 ? 'firebrick' : 'gold';
    }

    function getInnerCardElement({ id, name, ...lead } = {}) {
        const cloneTmpl = innerTmpl.content.cloneNode(true);

        const cloneTmplWrapper = cloneTmpl.querySelector('.card__inner');
        const cloneTmplTitle = cloneTmpl.querySelector('.card__inner-title');
        const cloneTmplId = cloneTmpl.querySelector('.card__inner-id');
        const cloneTmplDate = cloneTmpl.querySelector('.card__inner-date');

        if (lead.fetchError) {
            cloneTmplTitle.textContent = 'Ошибка';
            cloneTmplId.textContent = `
                Нет связи с сервером или карточки больше не существует.
            `;
            return cloneTmpl;
        }

        const timeStamp = lead.created_at * 1000;
        const taskTimeStamp = lead.closest_task_at * 1000;
        const date = new Date(timeStamp).toLocaleDateString('ru-RU');
        const taskStatus = getTaskStatus(taskTimeStamp);

        cloneTmplWrapper.setAttribute('style', '--status:' + taskStatus);
        cloneTmplTitle.setAttribute('title', name);
        cloneTmplTitle.textContent = name;
        cloneTmplId.textContent = '#' + id;
        cloneTmplDate.textContent = date;

        return cloneTmpl;
    }

    function initLoader(targetElem, dataAttr) {
        const tmpl = document.querySelector('#loader-template');
        const cloneTmpl = tmpl.content.cloneNode(true);

        cloneTmpl.querySelector('.loader-wrapper').setAttribute(dataAttr, '');
        targetElem.append(cloneTmpl);
    }

    function destroyLoader(dataAttr) {
        document.querySelector(`[${dataAttr}]`)?.remove();
    }
})();