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

import { Injectable } from '@angular/core';
import { Resolve, ActivatedRouteSnapshot } from '@angular/router';
import { DashboardService } from '../_services';
import { Observable } from 'rxjs';

@Injectable()
export class DashboardResolver implements Resolve<Observable<any>> {

  constructor(private dashboardService: DashboardService) {
  }

  resolve(route: ActivatedRouteSnapshot) {
    return this.dashboardService.getInterfaces();
  }
}
