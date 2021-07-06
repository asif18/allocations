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
import { Component, OnInit, OnDestroy } from '@angular/core';
import { DomSanitizer, SafeResourceUrl } from '@angular/platform-browser';
import { Subscription } from 'rxjs';
import { Title } from '@angular/platform-browser';
import { UserInfo } from '../../../_models';
import { AuthenticationService } from '../../../_services';


@Component({
  selector: 'app-web-logs',
  templateUrl: './web-logs.component.html',
  styleUrls: ['./web-logs.component.scss']
})
export class WebLogsComponent implements OnInit, OnDestroy {

  private subscriptions: Subscription[] = [];
  public iframeUrl: any = null;

  constructor(private titleService: Title, private authenticationService: AuthenticationService,
    private sanitizer: DomSanitizer) { }

  ngOnInit() {
    this.titleService.setTitle('Web Logs');
    this.subscriptions.push(this.authenticationService.userInfo.subscribe((userInfo: UserInfo) => {
      this.iframeUrl = this.sanitizer.bypassSecurityTrustResourceUrl(`http://${userInfo.dnsInfo.dnsIp}:${userInfo.dnsInfo.dnsPort}/admin`);
    }));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
