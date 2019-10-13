
var myApp = angular.module('myApp', ['ng-admin']);

var templateString = '<div class="row">\n' +
    '    <div class="col-lg-12">\n' +
    '        <div class="page-header">\n' +
    '            <h1>Loading...</h1>\n' +
    '        </div>\n' +
    '    </div>\n' +
    '</div>\n' +
    '\n' +
    '<div class="row dashboard-content">\n' +
    '<meta http-equiv = "refresh" content = "0; url = /__admin/#/mocks/list" />' +
    '</div>';

myApp.config(['NgAdminConfigurationProvider', 'RestangularProvider', function (nga, RestangularProvider) {

    var admin = nga.application('ARTURO MOCK').baseApiUrl('/__api/'); // main API endpoint

    admin.dashboard(nga.dashboard()
        .template(templateString)
    );

    RestangularProvider.setErrorInterceptor(function(error) {
        if(error.status === 401) {
            alert(error.data.error);
        }
        return true
    });

    var mocks = nga.entity('mocks');

    mocks.menuView().disable();
    mocks.dashboardView().disable();

    mocks.listView().filters([
        nga.field('detail').label('Title'),
        nga.field('url').label('Url'),
        nga.field('proxy').label('Proxy'),
        nga.field('method').label('Method')

    ]);

    mocks.listView().fields([
        nga.field('detail').label('Title').isDetailLink(true),
        nga.field('method').label('Method').isDetailLink(true),
        nga.field('state').label('Action')
            .cssClasses(function(entry) {
                if(entry) {
                    if (entry.values.state == 'record') {
                        return 'red-color';
                    } else if(entry.values.state == 'disable') {
                        return 'gray-color'
                    } else if(entry.values.state == 'enable') {
                        return 'green-color';
                    } else if(entry.values.state == 'proxy') {
                        return 'blue-color';
                    }
                }
            })
            .isDetailLink(true),
        nga.field('contentType').label('Content Type').isDetailLink(true),
        nga.field('url').label('Url')
            .map(function (value) {
                return value.substr(0, 70)+'...(and more)';
            })
            .isDetailLink(true),
    ]);
    mocks.creationView().fields([
        nga.field('detail').label('Title'),
        nga.field('url').label('Url'),
        nga.field('proxy').label('Proxy'),
        nga.field('state', 'choice').label('Action')
            .choices([
                { value: 'disable', label: 'disable' },
                { value: 'enable', label: 'enable' },
                { value: 'proxy', label: 'proxy' },
                { value: 'record', label: 'record' },
            ]),
        nga.field('method', 'choice').label('Method')
            .choices([
                { value: 'post', label: 'post' },
                { value: 'get', label: 'get' },
                { value: 'delete', label: 'delete' },
                { value: 'put', label: 'put' },
                { value: 'patch', label: 'patch' },
            ]),
        nga.field('contentType', 'choice').label('Content Type')
            .choices([
                { value: 'application/javascript', label: 'application/javascript'},
                { value: 'application/json', label: 'application/json'},
                { value: 'application/x-www-form-urlencoded', label: 'application/x-www-form-urlencoded'},
                { value: 'application/xml', label: 'application/xml'},
                { value: 'application/zip', label: 'application/zip'},
                { value: 'application/sql', label: 'application/sql'},
                { value: 'application/graphql', label: 'application/graphql'},
                { value: 'application/ld+json', label: 'application/ld+json'},
                { value: 'audio/mpeg', label: 'audio/mpeg'},
                { value: 'multipart/form-data', label: 'multipart/form-data'},
                { value: 'text/css', label: 'text/css'},
                { value: 'text/html', label: 'text/html'},
                { value: 'text/xml', label: 'text/xml'},
                { value: 'text/csv', label: 'text/csv'},
                { value: 'text/plain', label: 'text/plain'},
                { value: 'image/png', label: 'image/png'},
                { value: 'image/jpeg', label: 'image/jpeg'},
                { value: 'image/gif', label: 'image/gif'}
            ]),
        nga.field('statusCode', 'number').label('Status Code'),
        nga.field('payload', 'text').label('Body')
            .validation({ required: false, maxlength: 100000000000000000000 })

    ]);

    mocks.editionView().fields(mocks.creationView().fields());
    admin.addEntity(mocks);
    nga.configure(admin);
}]);

