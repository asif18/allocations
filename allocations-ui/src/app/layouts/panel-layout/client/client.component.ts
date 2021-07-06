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
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { ActivatedRoute } from '@angular/router';
import { Title } from '@angular/platform-browser';
import { SnackbarService, ClientService } from '../../../_services';
import { DefaultApiResponse } from '../../../_models';
import * as _ from 'lodash';

declare interface ClientData {
  name: string;
  businessName: string;
  email: string;
  phone: string;
  smsGateway: string;
  smsLimit: string;
  otpLogin: boolean;
  fbLogin: boolean;
  smsCampaign: boolean;
}

@Component({
  selector: 'app-client',
  templateUrl: './client.component.html',
  styleUrls: ['./client.component.scss']
})
export class ClientComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public isEditMode: boolean = false;
  private clientId: number = null;
  private encryptedClientId: string = null;
  public clientData: ClientData;
  public cardTitle: string = 'Add New Client';
  public smsGateways: Array<string>;

  constructor(
    private formBuilder: FormBuilder,
    private clientService: ClientService,
    private snackBar: SnackbarService,
    private route: ActivatedRoute,
    private titleService: Title) { }

  ngOnInit() {
    this.subscriptions.push(this.route.params.subscribe(params => {
      this.isEditMode = !!params.id;

      if (this.isEditMode) {
        this.titleService.setTitle('Edit Client');
        try {
          this.clientId = _.toNumber(atob(params.id));
          this.encryptedClientId = params.id;
          this.getClient(params.id);
        } catch (error) { this.isEditMode = false; noop(); }
      } else {
        this.titleService.setTitle('Add new client');
      }

      this.initForm();
    }));
  }

  private getClient(encryptedId: string) {
    this.isFormLoading = true;
    this.subscriptions.push(this.clientService.getClient(encryptedId)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (_.isObject(response.data)) {
          this.clientData = response.data;
          this.form.patchValue({
            name: this.clientData.name,
            businessName: this.clientData.businessName,
            email: this.clientData.email,
            phone: this.clientData.phone,
            smsGateway: this.clientData.smsGateway,
            smsLimit: this.clientData.smsLimit,
            otpLogin: this.clientData.otpLogin,
            fbLogin: this.clientData.fbLogin,
            smsCampaign: this.clientData.smsCampaign
          });

          // remove form controls if edit mode
          this.form.removeControl('username');
          this.form.removeControl('password');

          this.cardTitle = `Edit Client - ${this.clientData.businessName}`;
        }
      }));
  }

  private initForm(): void {
    this.smsGateways = ['TEXTLOCAL', 'VIDEOCON'];
    this.form = this.formBuilder.group({
      name: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3),
          Validators.pattern(/^[ a-zA-Z0-9]+$/)
        ]
      }),
      businessName: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3)
        ]
      }),
      email: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.email
        ],
      }),
      phone: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(10),
          Validators.minLength(10),
          Validators.pattern(/^[0-9]+$/)
        ]
      }),
      username: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      }),
      password: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(15),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      }),
      smsGateway: new FormControl(null, [
        Validators.required
      ]),
      smsLimit: new FormControl(null, {
        validators: [
          Validators.maxLength(10),
          Validators.pattern(/^[0-9]+$/)
        ]
      }),
      otpLogin: new FormControl(false),
      fbLogin: new FormControl(false),
      smsCampaign: new FormControl(false)
    });
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    let promise = null;
    if (!this.isEditMode) {
      this.form.value.password = btoa(this.form.value.password);
      promise = this.clientService.saveClient(this.form.value);
    } else {
      promise = this.clientService.updateClient(this.form.value, this.encryptedClientId);
    }

    this.subscriptions.push(promise.pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: any) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
