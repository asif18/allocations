/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Routes } from '@angular/router';
import { PanelLayoutComponent } from './panel-layout.component';
import { DashboardComponent } from './dashboard';

import { AllocationsComponent } from './allocations';
import { AllocationsListComponent } from './allocations-list';
import { AllocatedListComponent } from './allocated-list';
import { DeliveredListComponent } from './delivered-list'
import { YardsComponent } from './yards';
import { DestinationsComponent } from './destinations';
import { StaffsListComponent } from './staffs-list';
import { SettingsComponent } from './settings';
import { AuthenticationService } from '../../_services';
import { AuthGuard } from '../../_guards';
import { DashboardResolver } from '../../_classes';

export const PanelLayoutRoutes: Routes = [
  {
    path: 'panel',
    component: PanelLayoutComponent,
    resolve: { userInfo: AuthenticationService },
    children: [
      {
        path: '',
        redirectTo: 'dashboard',
        pathMatch: 'full'
      },
      {
        path: 'dashboard',
        component: DashboardComponent,
        canActivate: [AuthGuard],
        resolve: {
          dashboardData: DashboardResolver
        },
        data: {
          title: 'Dashboard',
          pageName: 'dashboard'
        }
      },
      {
        path: 'staffs-list',
        component: StaffsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Staffs List',
          pageName: 'staffsList'
        }
      },
      {
        path: 'allocations',
        component: AllocationsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Allocations',
          pageName: 'allocations'
        }
      },
      {
        path: 'not-allocated-list',
        component: AllocationsListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Not Allocated List',
          pageName: 'notAllocatedList'
        }
      },
      {
        path: 'allocated-list',
        component: AllocatedListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Allocated List',
          pageName: 'allocatedList'
        }
      },
      {
        path: 'delivered-list',
        component: DeliveredListComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Delivered List',
          pageName: 'deliveredList'
        }
      },
      {
        path: 'yards',
        component: YardsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Yards',
          pageName: 'yards'
        }
      },
      {
        path: 'destinations',
        component: DestinationsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Destinations',
          pageName: 'destinations'
        }
      },
      {
        path: 'yards',
        component: YardsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'Usage Timing Limits',
          pageName: 'yards'
        }
      },
      {
        path: 'general-settings',
        component: SettingsComponent,
        canActivate: [AuthGuard],
        data: {
          title: 'General Settings',
          pageName: 'generalSettings'
        }
      }
    ]
  }
];
