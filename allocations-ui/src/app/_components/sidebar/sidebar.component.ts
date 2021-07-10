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
import { Component, OnInit, OnDestroy, Input } from '@angular/core';
import { Router } from '@angular/router';
import { Subscription } from 'rxjs';
import { RouteInfo, DefaultApiResponse, UserInfo } from '../../_models';
import { AuthenticationService } from '../../_services';
import * as _ from 'lodash';

@Component({
  selector: 'app-sidebar',
  templateUrl: './sidebar.component.html',
  styleUrls: ['./sidebar.component.scss']
})
export class SidebarComponent implements OnInit, OnDestroy {

  @Input() window: Window;
  public menuItems: RouteInfo[] = null;
  public isAllSubMenusOpen: boolean = false;
  private subscriptions: Subscription[] = [];

  constructor(
    private router: Router,
    private authenticationService: AuthenticationService) {}

  ngOnInit() {
    this.subscriptions.push(this.authenticationService.getMenuItems()
      .subscribe((menuItems: DefaultApiResponse) => this.menuItems = _.sortBy(menuItems.data, ['order']))
    );
  }

  isMobileMenu(): boolean {
    return (window.innerWidth > 991) ? false : true;
  }

  menuAction(menuItem: RouteInfo): void {
    _.map(this.menuItems, (item: RouteInfo) => {
      if (item.caption !== menuItem.caption) {
        item.isSubMenuOpen = false;
      }
    });
    if (_.isNull(menuItem.path)) {
      menuItem.isSubMenuOpen = !menuItem.isSubMenuOpen;
    } else {
      this.router.navigateByUrl(menuItem.path);
    }
  }

  toggleSubMenus(): void {
    this.isAllSubMenusOpen = !this.isAllSubMenusOpen;
    _.map(this.menuItems, (item: RouteInfo) => item.isSubMenuOpen = this.isAllSubMenusOpen);
  }

  logout(): void {
    this.authenticationService.logout();
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
